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


namespace Tygh\Addons\YandexCheckout\HookHandlers;

class LoggingHookHandler
{
    /**
     * The "save_log" hook handler.
     *
     * Actions performed:
     *     - Saves log about payment operations.
     *
     * @param string $type    Log type
     * @param string $action  Log action
     * @param array  $data    Log data
     * @param int    $user_id User ID
     * @param array  $content Save content
     *
     * @psalm-param array{
     *  message: string
     * } $content
     *
     * @psalm-param array{
     *  message: string,
     *  context: array{_body: string, _headers:string},
     * } $data
     *
     * @psalm-suppress ReferenceConstraintViolation
     *
     * @param-out array{'yandex_checkout.request_body': false|string, 'yandex_checkout.request_headers': false|string, summary: string} $content
     *
     * @return void
     *
     * @see \fn_log_event()
     */
    public function onSaveLog($type, $action, array $data, $user_id, array &$content)
    {
        if ($type !== 'yandex_checkout') {
            return;
        }

        $content = [
            'summary'                         => $data['message'],
            'yandex_checkout.request_body'    => isset($data['context']['_body']) ? json_encode($data['context']['_body']) : '',
            'yandex_checkout.request_headers' => isset($data['context']['_headers']) ? json_encode($data['context']['_headers']) : '',
        ];
    }
}
