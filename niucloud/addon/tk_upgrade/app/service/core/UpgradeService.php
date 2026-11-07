<?php
// +----------------------------------------------------------------------
// | Author: TK
// +----------------------------------------------------------------------

namespace addon\tk_upgrade\app\service\core;

use app\model\addon\Addon;
use app\service\admin\install\InstallSystemService;
use app\service\core\addon\CoreAddonInstallService;
use core\base\BaseAdminService;
use core\exception\AddonException;
use core\exception\CommonException;
use think\facade\Db;
use ZipArchive;

/**
 * 本地上传更新服务层
 */
class UpgradeService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->tempPath = project_path() . 'niucloud/addon/upgrade_addon';
        $this->root_path = project_path();
        if ($this->site_id != 0) throw new CommonException('仅站点管理员可操作');
    }

    public function updateAdmin()
    {
        try {
            $key = 'niucloud_saas';
            //进行 admin wap 最新版替换
            $admin_url = (new CloudService())->updateVersion($key)['admin_url'] ?? '';
            if ($admin_url == '') throw new CommonException('暂无在线前端包,请云编译或手动编译');
            $this->createTempFileFrame($admin_url);
            if (is_dir($this->tempPath . DIRECTORY_SEPARATOR . 'admin')) {
                del_target_dir(public_path() . 'admin', true);
            }
            if (is_dir($this->tempPath . DIRECTORY_SEPARATOR . 'web')) {
                del_target_dir(public_path() . 'web', true);
            }
            if (is_dir($this->tempPath . DIRECTORY_SEPARATOR . 'wap')) {
                del_target_dir(public_path() . 'wap', true);
            }
            dir_copy($this->tempPath, public_path());
            return true;
        } catch (\Exception $e) {
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * Author: TK
     * Notes: 更新所有插件
     * @return true
     * @throws \Exception
     * 2025/9/16 13:53
     */
    public function updateAll()
    {
        $addon_list = $this->getAddonList();
        foreach ($addon_list as $k => $v) {
            if ($v['is_update'] == 1) {
                $this->installAddon($v['key']);
            }
        }
        return true;
    }

    /**
     * Author: TK
     * Notes:  安装所有插件
     * @return true
     * @throws \Exception
     * 2025/9/16 13:54
     */
    public function installAll()
    {
        $addon_list = $this->getAddonList();
        foreach ($addon_list as $k => $v) {
            if ($v['is_install'] == 0) {
                $this->installAddon($v['key']);
            }
        }
        return true;
    }

    public function updateFrame()
    {

        if (!extension_loaded('zip')) throw new AddonException('请安装php的zip扩展');
        //获取最新信息
        $key = 'niucloud_saas';
        $version = (new CloudService())->updateVersion($key);
        $addon_version = config('version')['version'];
        $res = $this->compareVersions($version['version'], $addon_version);
        if ($res == -1 && ($version['version'] != $addon_version)) {
            throw new CommonException('更新版本低于现在框架版本，中断操作');
        }
        $version_url = $version['all_url'] ?? '';
        $tk_url = $version['tk_url'] ?? '';
        $type = $version['type'] ?? '';
        if ($type == '') {
            $type = 'tk';
        }
        if ($version_url == '') throw new CommonException('暂无在线安装包');
        $file_url = $version_url;
        $this->createTempFileFrame($file_url);
        $addonPath = project_path();
        $exclude_files = ['.env.development', '.env.production', '.env', '.env.dev', '.env.product'];
        try {
            dir_copy($this->tempPath . '/' . 'niucloud-master', $addonPath, exclude_files: $exclude_files);
            //进行 admin wap 最新版替换
            $admin_url = (new CloudService())->updateVersion($key)['admin_url'] ?? '';
            if ($admin_url != '') {
                $this->createTempFileFrame($admin_url);
                if (is_dir($this->tempPath . DIRECTORY_SEPARATOR . 'admin')) {
                    del_target_dir(public_path() . 'admin', true);
                }
                if (is_dir($this->tempPath . DIRECTORY_SEPARATOR . 'web')) {
                    del_target_dir(public_path() . 'web', true);
                }
                if (is_dir($this->tempPath . DIRECTORY_SEPARATOR . 'wap')) {
                    del_target_dir(public_path() . 'wap', true);
                }
                dir_copy($this->tempPath, public_path());
            }
        } catch (\Exception $e) {
            throw new CommonException($e->getMessage());
        }
        (new CoreUpgradeService())->doUpgrade($addon_version);
        $this->recursiveRemoveDirectory($this->tempPath);
        //进行二开包覆盖
        if ($tk_url != '' && $type == 'niucloud') {
            $this->createTempFileFrame($tk_url);
            dir_copy($this->tempPath, project_path());
            $this->recursiveRemoveDirectory($this->tempPath);
            (new InstallSystemService())->install();
            sleep(3);
        }
        return true;
    }

    public function installAddon($key)
    {
        if (!extension_loaded('zip')) throw new AddonException('请安装php的zip扩展');
        if ($key == 'niucloud_saas') {
            $this->updateFrame();
            return true;
        }
        //获取最新信息
        $version = (new CloudService())->updateVersion($key);
        $version_url = $version['all_url'] ?? '';
        if ($version_url == '') throw new CommonException($key . ':无可用应用包或更新到期');
        $file_url = $version_url;
        $addon_update = $this->createTempFile($file_url);
        $addon_key = $addon_update['addon_key'];
        $addon_version = $addon_update['version'];
        $addonPath = project_path() . 'niucloud/addon/' . $addon_key;
        $addoning = Db::name('addon')->where(['key' => $addon_key, 'status' => 1])->findOrEmpty();
        if (empty($addoning) && $key != 'niucloud_saas') {
            $this->recursiveCopy($this->tempPath . '/' . $addon_key, $addonPath);
            //进行插件安装
            $res = (new \app\service\admin\addon\AddonService())->installCheck($addon_key);
            if ($res['is_pass'] != true) throw new CommonException('请设置整站目录权限 www  0777');
            (new CoreAddonInstallService($addon_key))->install();
            $data = [
                'type' => 'add'
            ];
        } else {
            $res = $this->compareVersions($addoning['version'], $addon_version);
            if ($res == 1 && $addon_key != 'tk_upgrade') {
                throw new CommonException('更新版本低于现有插件版本，中断操作');
            }
            $key = $addon_key . '_' . time();
            //(new UpgradeService($addon_key))->backupCode($key);
            //(new UpgradeService($addon_key))->backupSql($key);
            $this->recursiveCopy($this->tempPath . '/' . $addon_key, $addonPath);
            (new \addon\tk_upgrade\app\service\admin\UpgradeService($addon_key))->doUpgrade($addon_version, $addoning['version']);
            $data = [
                'type' => 'update'
            ];
        }
        $this->recursiveRemoveDirectory($this->tempPath);
        return $data;


    }

    public function createTempFileFrame($path)
    {
        $extractPath = $this->tempPath;
        $folderPath = $extractPath;
        if (is_dir($folderPath)) {
            $this->recursiveRemoveDirectory($folderPath);
        }

        // 下载网络文件到临时路径
        $tempZipPath = $extractPath . DIRECTORY_SEPARATOR . 'temp_addon.zip';
        // 确保目录存在
        if (!is_dir($extractPath)) {
            mkdir($extractPath, 0777, true);
        }

        // 分块下载大文件
        $chunkSize = 1024 * 1024; // 1MB 分块
        $fileHandle = fopen($tempZipPath, 'w');
        if ($fileHandle === false) {
            throw new CommonException('Failed to create temporary file.');
        }

        $curl = curl_init($path);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_FILE, $fileHandle);
        curl_setopt($curl, CURLOPT_TIMEOUT, 300); // 5分钟超时
        curl_setopt($curl, CURLOPT_NOPROGRESS, false);
        curl_setopt($curl, CURLOPT_PROGRESSFUNCTION, function ($resource, $downloadSize, $downloaded, $uploadSize, $uploaded) {
            // 可选：添加进度监控逻辑
            return 0;
        });

        $result = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
        fclose($fileHandle);

        if ($result === false) {
            throw new CommonException('Failed to download the file from URL: ' . $error);
        }

        $zip = new ZipArchive;
        $maxRetries = 3;
        $retryCount = 0;
        $extracted = false;

        // 检查 ZIP 文件是否存在
        if (!file_exists($tempZipPath)) {
            throw new CommonException('ZIP 文件不存在: ' . $tempZipPath);
        }

        // 检查 ZIP 文件是否可读
        if (!is_readable($tempZipPath)) {
            throw new CommonException('ZIP 文件不可读: ' . $tempZipPath);
        }

        // 重试机制
        while ($retryCount < $maxRetries && !$extracted) {
            if ($zip->open($tempZipPath) === TRUE) {
                try {
                    if ($zip->extractTo($extractPath)) {
                        $extracted = true;
                    } else {
                        throw new CommonException('解压失败，可能是 ZIP 文件损坏或目标目录不可写。');
                    }
                    $zip->close();
                } catch (\Exception $e) {
                    $zip->close();
                    $retryCount++;
                    if ($retryCount >= $maxRetries) {
                        throw new CommonException('解压失败，重试次数耗尽: ' . $e->getMessage());
                    }
                    // 等待 1 秒后重试
                    sleep(1);
                }
            } else {
                $retryCount++;
                if ($retryCount >= $maxRetries) {
                    throw new CommonException('无法打开 ZIP 文件，重试次数耗尽: ' . $zip->getStatusString());
                }
                // 等待 1 秒后重试
                sleep(1);
            }
        }

        if (!$extracted) {
            throw new CommonException('解压失败，请检查 ZIP 文件或目标目录权限。');
        }

        // 清理临时 ZIP 文件
        unlink($tempZipPath);

        $directory = $extractPath;
        if (substr($directory, -1) !== DIRECTORY_SEPARATOR) {
            $directory .= DIRECTORY_SEPARATOR;
        }
        $files_and_dirs = scandir($directory);
        if ($files_and_dirs === false) {
            throw new CommonException('无法读取解压目录内容');
        }
        $folders = array_filter($files_and_dirs, function ($item) use ($directory) {
            return is_dir($directory . DIRECTORY_SEPARATOR . $item) && !in_array($item, array('.', '..'));
        });
        if (count($folders) < 1) {
            throw new CommonException('压缩文件格式错误，没有找到目录');
        }
        return true;
    }

    public function createTempFile($path)
    {
        $extractPath = $this->tempPath;
        $folderPath = $extractPath;
        if (is_dir($folderPath)) {
            $this->recursiveRemoveDirectory($folderPath);
        }

        // 下载网络文件到临时路径
        $tempZipPath = $extractPath . DIRECTORY_SEPARATOR . 'temp_addon.zip';
        // 确保目录存在
        if (!is_dir($extractPath)) {
            mkdir($extractPath, 0777, true);
        }
        $fileContent = @file_get_contents($path);
        if ($fileContent === false) {
            throw new CommonException('Failed to download the file from URL.');
        }
        file_put_contents($tempZipPath, $fileContent);

        $zip = new ZipArchive;
        $maxRetries = 3;
        $retryCount = 0;
        $extracted = false;

        // 检查 ZIP 文件是否存在
        if (!file_exists($tempZipPath)) {
            throw new CommonException('ZIP 文件不存在: ' . $tempZipPath);
        }

        // 检查 ZIP 文件是否可读
        if (!is_readable($tempZipPath)) {
            throw new CommonException('ZIP 文件不可读: ' . $tempZipPath);
        }

        // 重试机制
        while ($retryCount < $maxRetries && !$extracted) {
            if ($zip->open($tempZipPath) === TRUE) {
                try {
                    if ($zip->extractTo($extractPath)) {
                        $extracted = true;
                    } else {
                        throw new CommonException('解压失败，可能是 ZIP 文件损坏或目标目录不可写。');
                    }
                    $zip->close();
                } catch (\Exception $e) {
                    $zip->close();
                    $retryCount++;
                    if ($retryCount >= $maxRetries) {
                        throw new CommonException('解压失败，重试次数耗尽: ' . $e->getMessage());
                    }
                    // 等待 1 秒后重试
                    sleep(1);
                }
            } else {
                $retryCount++;
                if ($retryCount >= $maxRetries) {
                    throw new CommonException('无法打开 ZIP 文件，重试次数耗尽: ' . $zip->getStatusString());
                }
                // 等待 1 秒后重试
                sleep(1);
            }
        }

        if (!$extracted) {
            throw new CommonException('解压失败，请检查 ZIP 文件或目标目录权限。');
        }

        // 清理临时 ZIP 文件
        unlink($tempZipPath);

        $directory = $extractPath;
        if (substr($directory, -1) !== DIRECTORY_SEPARATOR) {
            $directory .= DIRECTORY_SEPARATOR;
        }
        $files_and_dirs = scandir($directory);

        $folders = array_filter($files_and_dirs, function ($item) use ($directory) {
            return is_dir($directory . DIRECTORY_SEPARATOR . $item) && !in_array($item, array('.', '..'));
        });
        if (count($folders) != 1) throw new \Exception('插件格式错误');
        foreach ($folders as $k => $v) {
            $addon_key = $v;
        }
        //检查插件json 文件
        if (!file_exists($extractPath . '/' . $addon_key . '/info.json')) throw new \Exception('请上传正确的压缩包，JSON文件不存在');
        $addonString = @file_get_contents($extractPath . '/' . $addon_key . '/info.json');
        $addonArr = json_decode($addonString, true);
        if ($addon_key != $addonArr['key']) throw new \Exception('插件目录和key不匹配');
        return ['addon_key' => $addon_key, 'version' => $addonArr['version']];
    }

    public function recursiveRemoveDirectory($directory)
    {
        $directory = realpath($directory);
        if ($directory === false || !is_dir($directory)) {
            return false;
        }
        $handle = opendir($directory);
        if (!$handle) {
            return false;
        }
        while (false !== ($entry = readdir($handle))) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            $entryPath = $directory . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($entryPath)) {
                $this->recursiveRemoveDirectory($entryPath);
            } else {
                unlink($entryPath);
            }
        }
        closedir($handle);
        return rmdir($directory);
    }


    public function recursiveCopy($source, $destination)
    {
        // 检查目标目录是否存在，如果不存在则创建
        if (!is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        // 遍历源目录中的所有文件和子目录
        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            // 构建源文件和目标文件的完整路径
            $sourcePath = $source . DIRECTORY_SEPARATOR . $file;
            $destinationPath = $destination . DIRECTORY_SEPARATOR . $file;

            // 检查是文件还是目录
            if (is_dir($sourcePath)) {
                // 如果是目录，递归复制
                $this->recursiveCopy($sourcePath, $destinationPath);
            } else {
                // 如果是文件，直接复制
                copy($sourcePath, $destinationPath);
            }
        }
        closedir($dir);
    }

    public function getVersionList($key)
    {
        $version = (new CloudService())->versionList($key);
        return $version;
    }

    public function checkAuth()
    {
        $data = (new CloudService())->auth();
        if($data==''){
            throw new CommonException('TKCODE:该功能需要授权才能使用');
        }
        $data['frame_version'] = config('version')['version'];
        $frame_version_new = (new  CloudService())->updateVersion('niucloud_saas')['version'] ?? '';
        $data['frame_version_cloud'] = $frame_version_new;
        if ($data['frame_version_cloud'] == '') {
            $data['frame_update'] = 0;
        } else {
            $c = $this->compareVersions($frame_version_new, $data['frame_version']);
            if ($c == 1) {
                $data['frame_update'] = 1;
            } else {
                $data['frame_update'] = 0;
            }
        }

        return $data;
    }

    public function getAddonList()
    {
        $cloud_list = (new CloudService())->getAddonList();
        $addonModel = new Addon();
        //和本地版本比较
        foreach ($cloud_list as $k => $v) {
            $local = $addonModel->where(['key' => $v['key']])->findOrEmpty();
            //进行本地文件检测，如果文件丢失需要进行重新拉取
            $addon_dir = project_path() . 'niucloud/addon/' . $v['key'];
            if (is_dir($addon_dir)) {
                $local_file = 1;
            } else {
                $local_file = 0;
            }
            if ($local->isEmpty()) {
                $cloud_list[$k]['is_install'] = 0;
                $cloud_list[$k]['local_version'] = '';
                $cloud_list[$k]['is_update'] = 0;
            } else {
                $cloud_list[$k]['is_install'] = 1;
                $cloud_list[$k]['local_version'] = $local['version'];
                if ($v['version'] == $local['version']) {
                    $cloud_list[$k]['is_update'] = 0;
                }
                if ($v['version'] == '' || $v['version'] == null) {
                    $cloud_list[$k]['is_version'] = 0;
                    $cloud_list[$k]['is_update'] = 0;
                } else {
                    $cloud_list[$k]['is_version'] = 1;
                    $is_new = $this->compareVersions($v['version'], $local['version']);
                    if ($is_new == 1) {
                        $cloud_list[$k]['is_update'] = 1;
                    } else {
                        $cloud_list[$k]['is_update'] = 0;
                    }
                }
                if ($local_file == 0) {
                    $cloud_list[$k]['is_update'] = 0;
                    $cloud_list[$k]['is_install'] = 0;
                }
            }
        }
        return $cloud_list;
    }

    public function compareVersions($version1, $version2)
    {
        $version1 = version_to_int($version1);
        $version2 = version_to_int($version2);
        if ($version1 < $version2) {
            return -1;
        } elseif ($version1 > $version2) {
            return 1;
        } else {
            return -1;
        }

    }
}