<?php
/**
 * Oss.php
 *
 * User: sunanzhi
 * Date: 2021.6.18
 * Email: <sunanzhi@kurogame.com>
 */

namespace kuro\sdk\center;


use kuro\sdk\CClient;

class Oss
{
    public function uploadMultiFile(string $scene, array $files): array
    {
        return (new CClient())->setFiles($files)->request('center', 'Oss/uploadMultiFile', false, 'array', $scene);
    }
}