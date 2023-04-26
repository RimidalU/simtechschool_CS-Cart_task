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


use Tygh\Common\OperationResult;
use Tygh\Tools\Archiver;
use Exception;
use ZipArchive;

class UnzipImportFileCommandHandler
{
    /**
     * @var \Tygh\Tools\Archiver
     */
    private $archiver;

    /**
     * UnzipImportFileCommandHandler constructor.
     *
     * @param \Tygh\Tools\Archiver $archiver Archiver instance
     */
    public function __construct(Archiver $archiver)
    {
        $this->archiver = $archiver;
    }

    /**
     * Executes unziping import file
     *
     * @param \Tygh\Addons\CommerceML\Commands\UnzipImportFileCommand $command Command instance
     *
     * @return \Tygh\Common\OperationResult
     */
    public function handle(UnzipImportFileCommand $command)
    {
        $result = new OperationResult();

        try {
            if ($command->validate) {
                if (!$this->archiveIsValid($command->file_path)) {
                    $result->setSuccess(false);
                    return $result;
                }
            }

            if ($command->get_filelist) {
                $files = $this->archiver->getFiles($command->file_path);
                $result->setData($files, 'file_list');
            }

            $this->archiver->extractTo($command->file_path, $command->dir_path);
            $result->setSuccess(true);
        } catch (Exception $e) {
            $result->setErrors([$e->getMessage()]);
        }

        if ($command->remove_file && $result->isSuccess()) {
            fn_rm($command->file_path);
        }

        return $result;
    }

    /**
     * Checks the integrity of the archive
     *
     * @param string $zip_path Path to zip
     *
     * @return bool
     */
    private function archiveIsValid($zip_path)
    {
        $zip = new ZipArchive();
        $status = $zip->open($zip_path);

        if ($status === true) {
            $zip->close();
        }

        return $status === true;
    }
}
