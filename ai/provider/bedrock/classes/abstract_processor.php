<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace aiprovider_bedrock;

use Aws\BedrockRuntime\BedrockRuntimeClient;
use core\http_client;
use core_ai\aiactions\responses\response_base;
use core_ai\process_base;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Abstract class for Bedrock processors.
 *
 * @package    aiprovider_bedrock
 * @copyright  2025 Davide Ferro <dferro@meeplesrl.it>, Angelo Calò <acalo@meeplesrl.it>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class abstract_processor extends process_base {
    /**
     * Get the Bedrock client.
     *
     * @return BedrockRuntimeClient
     */
    protected function get_bedrock_client(): BedrockRuntimeClient {
        $region = get_config('aiprovider_bedrock', 'region');
        $accesskey = get_config('aiprovider_bedrock', 'accesskeyid');
        $secretkey = get_config('aiprovider_bedrock', 'secretaccesskey');

        return new BedrockRuntimeClient([
            'version' => 'latest',
            'region' => $region,
            'credentials' => [
                'key' => $accesskey,
                'secret' => $secretkey,
            ],
        ]);
    }

    /**
     * Get the name of the model to use.
     *
     * @return string
     */
    abstract protected function get_model(): string;

    /**
     * Get the system instructions.
     *
     * @return string
     */
    protected function get_system_instruction(): string {
        return $this->action::get_system_instruction();
    }

    /**
     * Create the request parameters to send to the Bedrock API.
     *
     * @param string $userid The user id.
     * @return array The request parameters to send to the Bedrock API.
     */
    abstract protected function create_request_params(
        string $userid,
    ): array;

    /**
     * Handle a successful response from the external AI api.
     *
     * @param array $response The response from the Bedrock API.
     * @return array The processed response.
     */
    abstract protected function handle_api_success(array $response): array;

    /**
     * Query the AI API using the Bedrock client.
     *
     * @return array The response from the API.
     */
    protected function query_ai_api(): array {
        try {
            $userid = $this->provider->generate_userid($this->action->get_configuration('userid'));
            $client = $this->get_bedrock_client();
            $model = $this->get_model();
            $params = $this->create_request_params($userid);

            $result = $client->invokeModel([
                'modelId' => $model,
                'body' => json_encode($params),
                'contentType' => 'application/json',
                'accept' => 'application/json',
            ]);

            $response = json_decode($result->get('body')->getContents(), true);
            return $this->handle_api_success($response);

        } catch (\Exception $e) {
            return [
                'success' => false,
                'errorcode' => $e->getCode() ?: 500,
                'errormessage' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle an error from the external AI api.
     *
     * @param array $errorinfo The error information.
     * @return array The error response.
     */
    protected function handle_api_error(array $errorinfo): array {
        return [
            'success' => false,
            'errorcode' => $errorinfo['code'] ?? 500,
            'errormessage' => $errorinfo['message'] ?? get_string('error:unknownerror', 'aiprovider_bedrock'),
        ];
    }
}
