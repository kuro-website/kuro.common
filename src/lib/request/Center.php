<?php
/**
 * Center.php
 *
 * User: sunanzhi
 * Date: 2021.3.23
 * Email: <sunanzhi@kurogame.com>
 */

namespace kuro\lib\request;

use kuro\dto\AdminDTO;
use kuro\exception\AuthException;
use kuro\exception\ForbiddenException;
use kuro\exception\NotFoundException;
use GuzzleHttp\Client;
use think\facade\Config;
use think\facade\Log;

/**
 * Class Center
 * @package app\lib\request
 */
class Center
{
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * oss 上传文件
     *
     * @param string $filename
     * @param array $header
     * @param array $data
     * @param array $config
     * @return mixed
     *
     * @author sunanzhi <sunanzhi@kurogame.com>
     * @since 2021.3.23 14:20
     */
    public function ossUploadFile(string $filename, array $header, array $data, array $config)
    {
        $body = [
            'type' => 1,
            'data' => [
                'filename' => $filename,
                'header' => $header,
                'data' => $data,
                'config' => $config
            ],
        ];
        try {
            $response = $this->client->post(Config::get('app.other.centerServerApi') . '/oss/uploadFile', [
                'json' => $body
            ]);
        } catch (\Throwable $e) {
            Log::write($e->getMessage(), 'error');
            exit;
        }
        $content = $response->getBody()->getContents();

        $res = json_decode($content, true);

        if ($res['_code'] != 0) {
            Log::write($res['_message'], 'error');
            exit;
        }

        return $res['_data'];
    }

    /**
     * 获取角色信息
     *
     * @param string $authorization
     * @throws AuthException
     * @return array
     *
     * @author sunanzhi <sunanzhi@kurogame.com>
     * @since 2021.3.30 11:03
     */
    public function getRoleInfo(string $authorization): array
    {
        try {
            $response = $this->client->post(Config::get('app.other.centerServerApi').'/role/info', [
                'headers' => ['Authorization' => $authorization]
            ]);
        } catch (\Throwable $e) {
            throw new AuthException('authorization invalid');
        }
        if($response->getStatusCode() != 200) {
            throw new AuthException('authorization invalid');
        }
        $content = $response->getBody()->getContents();

        $res = json_decode($content, true);

        if($res['_code'] != 0) {
            throw new AuthException($res['_message']);
        }

        return $res['_data'];
    }

    /**
     * 校验token是否有效
     *
     * @param string $authorization
     * @param string $router
     * @param array $params
     * @throws \Exception
     *
     * @author sunanzhi <sunanzhi@kurogame.com>
     * @since 2021.3.30 10:43
     */
    public function checkToken(string $authorization, string $router, array $params = [])
    {
        $body = [
            'router' => $router,
            'params' => $params,
            'deviceInfo' => getBrowserInfo(),
        ];
        try {
            $response = $this->client->post(Config::get('app.other.centerServerApi').'/auth/check', [
                'headers' => ['Authorization' => $authorization],
                'json' => $body
            ]);
        } catch (\Throwable $e) {
            if(strstr($e->getMessage(), 'ForbiddenException')) {
                throw new ForbiddenException('Forbidden');
            } else if(strstr($e->getMessage(), 'AuthException')) {
                throw new AuthException('authorization invalid');
            } else if(strstr($e->getMessage(), 'NotFoundException')) {
                throw new NotFoundException('not found');
            } else {
                throw new AuthException($e->getMessage());
            }
        }
        if($response->getStatusCode() != 200) {
            throw new AuthException('authorization invalid');
        }
        $content = $response->getBody()->getContents();

        $res = json_decode($content, true);
        if($res['_code'] != 0) {
            throw new AuthException($res['_message']);
        }
        $adminDTO = new AdminDTO();
        $adminDTO->setAdminId($res['_data']['adminId']);

        app()->admin = $adminDTO;
    }
}