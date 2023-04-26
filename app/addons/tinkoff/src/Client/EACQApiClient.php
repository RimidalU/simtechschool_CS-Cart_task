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

namespace Tygh\Addons\Tinkoff\Client;

defined('BOOTSTRAP') or die('Access denied');

use Tygh\Enum\NotificationSeverity;
use Tygh\Http;

/**
 * Class contains methods for sending requests for EACQ Tinkoff API.
 */
class EACQApiClient
{
    /** @var string $endpoint */
    protected $endpoint = 'https://securepay.tinkoff.ru/v2/';

    const PROTOCOL_VERSION = '1.32';

    /**
     * Makes request to Tinkoff API endpoint.
     *
     * @phpcs:disable SlevomatCodingStandard.TypeHints.DisallowMixedTypeHint.DisallowedMixedTypeHint
     *
     * @param string                $path    Name of request method at Tinkoff API.
     * @param string                $method  Type of request to Tinkoff API.
     * @param array<string, string> $params  Encoded body of request.
     * @param array<string, string> $headers Request headers.
     *
     * @return array<string, string>|string
     */
    protected function execute($path, $method, array $params, array $headers = [])
    {
        $headers = array_merge([
            'Content-type: application/json',
        ], $headers);
        $params = json_encode($params);
        switch ($method) {
            case Http::GET:
                $answer = Http::get($this->endpoint . $path, $params, ['headers' => $headers]);
                break;
            case Http::POST:
                $answer = Http::post($this->endpoint . $path, $params, ['headers' => $headers]);
                break;
            default:
                $answer = '';
                break;
        }
        $answer = json_decode($answer, true);
        return $answer;
    }

    /**
     * Handling error response.
     *
     * @param array<string, string> $response Response from Tinkoff API.
     *
     * @return void
     */
    public function handleError(array $response)
    {
        fn_set_notification(NotificationSeverity::ERROR, $response['Message'], $response['Details']);
    }

    /**
     * Calculates request token.
     *
     * @param array<string, string|int|array<string>> $request_body           Current request body state.
     * @param string                                  $password               Terminal password.
     * @param array<string>                           $unsupported_parameters Fields of request which should not be part of token calculation.
     */
    public function generateRequestToken(array $request_body, $password, array $unsupported_parameters = []): string
    {
        $result = '';
        $filter_func = static function ($key, $value) use ($unsupported_parameters) {
            if (empty($unsupported_parameters)) {
                return is_array($value);
            }
            return in_array($key, $unsupported_parameters);
        };
        $request_body['Password'] = $password;
        ksort($request_body);
        foreach ($request_body as $request_parameter => $parameter_value) {
            if ($filter_func($request_parameter, $parameter_value)) {
                continue;
            }
            $result .= $parameter_value;
        }
        return (string) hash('sha256', $result);
    }
}
