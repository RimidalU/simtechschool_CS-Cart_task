<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/


namespace Tygh\Addons\CommerceML\Commands;


use Tygh\Addons\CommerceML\Dto\ImportDto;
use Tygh\Addons\CommerceML\ServiceProvider;
use Tygh\Addons\CommerceML\Storages\ImportStorage;
use Tygh\Addons\CommerceML\Xml\Exceptions\XmlParserException;
use Tygh\Addons\CommerceML\Xml\SimpleXmlElement;
use Tygh\Addons\CommerceML\Xml\XmlParser;
use Tygh\Common\OperationResult;
use Tygh\Enum\SyncDataStatuses;
use Tygh\Exceptions\DeveloperException;

/**
 * Class CreateImportCommandHandler
 *
 * @package Tygh\Addons\CommerceML\Commands
 */
class CreateImportCommandHandler
{
    /**
     * @var callable
     */
    private $import_storage_factory;

    /**
     * @var \Tygh\Addons\CommerceML\Xml\XmlParser
     */
    private $xml_parser;

    /**
     * @var callable
     */
    private $xml_parser_callbacks_factory;

    /**
     * CreateImportCommandHandler constructor.
     *
     * @param callable                              $import_storage_factory       Import storage factory
     * @param \Tygh\Addons\CommerceML\Xml\XmlParser $xml_parser                   XML parser
     * @param callable                              $xml_parser_callbacks_factory Xml parser callbacks factory
     */
    public function __construct(
        callable $import_storage_factory,
        XmlParser $xml_parser,
        callable $xml_parser_callbacks_factory
    ) {
        $this->import_storage_factory = $import_storage_factory;
        $this->xml_parser = $xml_parser;
        $this->xml_parser_callbacks_factory = $xml_parser_callbacks_factory;
    }

    /**
     * Executes creating import
     *
     * @param \Tygh\Addons\CommerceML\Commands\CreateImportCommand $command Command instance
     *
     * @return \Tygh\Common\OperationResult
     *
     * @throws \Tygh\Addons\CommerceML\Xml\Exceptions\XmlParserException If parsing failed.
     * @throws \Tygh\Exceptions\DeveloperException                       If factory return no ImportStorage.
     */
    public function handle(CreateImportCommand $command)
    {
        $result = new OperationResult();

        foreach ($command->xml_file_paths as $path) {
            if (!file_exists($path)) {
                $result->addError('xml', __('commerceml.the_file_does_not_exist', ['[file]' => $path]));
                return $result;
            }
        }

        $file_paths = $this->sortFiles($command->xml_file_paths);
        $import_dto = $this->getImportDto($command, reset($file_paths));
        $import_storage = $this->createImportStorage($import_dto);
        $callbacks = $this->getCallbacks($import_storage, $command->import_type);

        try {
            foreach ($file_paths as $file_path) {
                $this->xml_parser->parse($file_path, $callbacks);
            }

            $import_storage->saveImport();

            $result->setSuccess(true);
            $result->setData($import_storage->getImport(), 'import');
            $result->setData($import_storage, 'import_storage');
        } catch (XmlParserException $exception) {
            $import_storage->removeImport();
            $result->addError('xml', $exception->getMessage());
        }

        return $result;
    }

    /**
     * @param array<array-key, string> $file_paths Xml file paths
     *
     * @return array<array-key, string>
     */
    private function sortFiles(array $file_paths)
    {
        usort($file_paths, static function ($a_file_path, $b_file_path) {
            if (strpos($a_file_path, 'import') !== false) {
                return -1;
            }
            if (strpos($b_file_path, 'import') !== false) {
                return 1;
            }
            if (strpos($a_file_path, 'offer') !== false) {
                return -1;
            }
            if (strpos($b_file_path, 'offer') !== false) {
                return 1;
            }

            return 0;
        });

        return $file_paths;
    }

    /**
     * Gets xml parser callbacks
     *
     * @param \Tygh\Addons\CommerceML\Storages\ImportStorage $import_storage Import storage instance
     * @param string                                         $import_type    Import type
     *
     * @return array<string, callable>
     */
    private function getCallbacks(ImportStorage $import_storage, $import_type)
    {
        /** @var array<string, callable> $xml_parser_callbacks */
        $xml_parser_callbacks = call_user_func($this->xml_parser_callbacks_factory, $import_type);

        foreach ($xml_parser_callbacks as $key => $callback) {
            $xml_parser_callbacks[$key] = static function (SimpleXmlElement $xml) use ($callback, $import_storage) {
                $callback($xml, $import_storage);
            };
        }

        return $xml_parser_callbacks;
    }

    /**
     * Creates import storage
     *
     * @param \Tygh\Addons\CommerceML\Dto\ImportDto $import Import Dto
     *
     * @return \Tygh\Addons\CommerceML\Storages\ImportStorage
     *
     * @throws \Tygh\Exceptions\DeveloperException If factory return no ImportStorage.
     */
    private function createImportStorage(ImportDto $import)
    {
        /** @var ImportStorage $import_storage */
        $import_storage = call_user_func($this->import_storage_factory, $import);

        if (!$import_storage instanceof ImportStorage) {
            throw new DeveloperException();
        }

        $import_storage->saveImport();

        return $import_storage;
    }

    /**
     * Load or create Import dto
     *
     * @param \Tygh\Addons\CommerceML\Commands\CreateImportCommand $command   Command instance
     * @param string                                               $file_path Import file path
     *
     * @return \Tygh\Addons\CommerceML\Dto\ImportDto
     *
     * @throws \Tygh\Addons\CommerceML\Xml\Exceptions\XmlParserException If parsing failed.
     */
    private function getImportDto(CreateImportCommand $command, $file_path)
    {
        $import = null;
        $catalog_id = '';
        $is_start_import = false;

        $callbacks = [
            'classifier/id' => static function (SimpleXmlElement $xml) use (&$is_start_import, &$catalog_id) {
                $catalog_id = (string) $xml;
                $is_start_import = true;
            },
            'catalog/id' => static function (SimpleXmlElement $xml) use (&$import, &$catalog_id) {
                $catalog_id = (string) $xml;
                $import = ServiceProvider::getImportRepository()->findByCatalogId($catalog_id);
            },
        ];

        (new XmlParser())->parse($file_path, $callbacks);

        if ($is_start_import || $import === null) {
            $import = new ImportDto();
            $import->company_id = $command->company_id;
            $import->catalog_id = $catalog_id;
            $import->user_id = $command->user_id;
            $import->import_key = $command->import_key;
            $import->status = SyncDataStatuses::STATUS_NEW;
            $import->type = $command->import_type;
            $import->created_at = time();
            $import->updated_at = time();
        }

        return $import;
    }
}
