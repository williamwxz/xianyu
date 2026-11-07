<?php
// +----------------------------------------------------------------------
// | Author: TK
// +----------------------------------------------------------------------

namespace addon\tk_upgrade\app\service\admin;

use app\service\admin\generator\GenerateService;
use app\service\core\addon\CoreAddonService;
use app\service\core\addon\CoreDependService;
use app\service\core\addon\WapTrait;
use app\service\core\menu\CoreMenuService;
use app\service\core\schedule\CoreScheduleInstallService;
use core\base\BaseAdminService;
use core\exception\CommonException;
use core\util\DbBackup;
use think\facade\Db;
use think\facade\Log;

/**
 * 本地上传更新服务层
 */
class UpgradeService extends BaseAdminService
{

    use WapTrait;

    public function __construct($addon_key)
    {
        parent::__construct();
        $this->tempPath = project_path() . 'niucloud/addon/upgrade_addon';
        $this->addon_key = $addon_key;
    }

    public function doUpgrade($install_version, $installed_version)
    {
        $this->executeUpgrade($install_version, $installed_version);
        $this->installDir();
        $this->installDepend();
        $this->installWap();
        $this->installSchedule();
        $this->refreshMenu();
        Db::name('addon')->where('key', $this->addon_key)->update(['version' => $install_version]);
    }

    /**
     * @Notes:备份代码
     * @Interface backupCode
     * @return true
     * @author: TK
     * @Time: 2024/7/17   上午9:41
     */
    public function backupCode($key)
    {
        $backup_dir = project_path() . DIRECTORY_SEPARATOR . 'upgrade/' . $key . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR;
        // 创建目录
        dir_mkdir($backup_dir);
        // 备份admin
        dir_copy(project_path() . 'admin', $backup_dir . 'admin', exclude_dirs: ['.vscode', 'node_modules', 'dist']);
        // 备份uni-app
        dir_copy(project_path() . 'uni-app', $backup_dir . 'uni-app', exclude_dirs: ['node_modules', 'dist']);
        // 备份web
        dir_copy(project_path() . 'web', $backup_dir . 'web', exclude_dirs: ['node_modules', '.nuxt', '.output']);
        // 备份niucloud
        $niucloud_dir = $backup_dir . 'niucloud' . DIRECTORY_SEPARATOR;
        $addon = $this->addon_key;
        $addon_dir = project_path() . 'niucloud' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $addon;
        $to_addon_dir = $niucloud_dir . 'addon' . DIRECTORY_SEPARATOR . $addon;
        dir_copy($addon_dir, $to_addon_dir);
        // 备份前端文件
        if (is_dir(public_path() . 'admin')) {
            dir_copy(public_path() . 'admin', $niucloud_dir . 'public' . DIRECTORY_SEPARATOR . 'admin');
        }
        if (is_dir(public_path() . 'wap')) {
            dir_copy(public_path() . 'wap', $niucloud_dir . 'public' . DIRECTORY_SEPARATOR . 'wap');
        }
        if (is_dir(public_path() . 'web')) {
            dir_copy(public_path() . 'web', $niucloud_dir . 'public' . DIRECTORY_SEPARATOR . 'web');
        }
        return true;
    }

    /**
     * @Notes:备份数据库
     * @Interface backupSql
     * @return true
     * @throws \Exception
     * @author: TK
     * @Time: 2024/7/17   上午9:41
     */
    public function backupSql($key)
    {
        return true;
    }

    /**
     * @Notes:进行资源文件替换
     * @Interface installDir
     * @return true
     * @author: TK
     * @Time: 2024/7/17   上午11:59
     */
    public function installDir()
    {
        $this->install_addon_path = project_path() . 'niucloud/addon/' . $this->addon_key . DIRECTORY_SEPARATOR;
        $from_admin_dir = $this->install_addon_path . 'admin' . DIRECTORY_SEPARATOR;
        $from_web_dir = $this->install_addon_path . 'web' . DIRECTORY_SEPARATOR;
        $from_wap_dir = $this->install_addon_path . 'uni-app' . DIRECTORY_SEPARATOR;
        $from_resource_dir = $this->install_addon_path . 'resource' . DIRECTORY_SEPARATOR;

        // 放入的文件
        $to_admin_dir = project_path() . 'admin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon_key . DIRECTORY_SEPARATOR;
        $to_web_dir = project_path() . 'web' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon_key . DIRECTORY_SEPARATOR;
        $to_wap_dir = project_path() . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon_key . DIRECTORY_SEPARATOR;
        $to_resource_dir = public_path() . 'addon' . DIRECTORY_SEPARATOR . $this->addon_key . DIRECTORY_SEPARATOR;

        // 安装admin管理端
        if (file_exists($from_admin_dir)) {
            dir_copy($from_admin_dir, $to_admin_dir);
            // 判断图标目录是否存在
            if (is_dir($from_admin_dir . 'icon')) {
                $addon_icon_dir = project_path() . 'admin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'styles' . DIRECTORY_SEPARATOR . 'icon' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon_key;
                dir_copy($from_admin_dir . 'icon', $addon_icon_dir);
            }
            // 编译后台图标库文件
            $this->compileAdminIcon();
        }

        // 安装电脑端
        if (file_exists($from_web_dir)) {
            // 安装布局文件
            $layout = $from_web_dir . 'layouts';
            if (is_dir($layout)) {
                dir_copy($layout, project_path() . 'web' . DIRECTORY_SEPARATOR . 'layouts');
                del_target_dir($layout, true);
            }
            dir_copy($from_web_dir, $to_web_dir);
        }

        // 安装手机端
        if (file_exists($from_wap_dir)) {
            dir_copy($from_wap_dir, $to_wap_dir);
        }

        //安装资源文件
        if (file_exists($from_resource_dir)) {
            dir_copy($from_resource_dir, $to_resource_dir);
        }

        return true;
    }

    /**
     * 编译后台图标库文件
     * 图标开发注意事项，不能占用  iconfont、icon 关键词（会跟系统图标冲突），建议增加业务前缀，比如 旅游业：recharge
     * @return bool
     */
    public function compileAdminIcon()
    {
        $compile_path = project_path() . str_replace('/', DIRECTORY_SEPARATOR, 'admin/src/styles/icon/');

        $content = "";
        $root_path = $compile_path . 'addon'; // 插件图标根目录
        $file_arr = getFileMap($root_path);
        if (!empty($file_arr)) {
            foreach ($file_arr as $ck => $cv) {
                if (str_contains($cv, '.css')) {
                    $path = str_replace($root_path . '/', '', $ck);
                    $path = str_replace('/.css', '', $path);
                    $content .= "@import \"addon/{$path}\";\n";
                }
            }
        }
        file_put_contents($compile_path . 'addon-iconfont.css', $content);
        return true;
    }

    /**
     * @Notes:合并依赖
     * @Interface installDepend
     * @return true
     * @author: TK
     * @Time: 2024/7/17   下午12:01
     */
    public function installDepend()
    {
        (new CoreDependService())->installDepend($this->addon_key);
        return true;
    }

    public function installWap()
    {
        $this->addon = $this->addon_key;
        // 编译 diy-group 自定义组件代码文件
        $this->compileDiyComponentsCode(project_path() . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, $this->addon);

        // 编译 fixed-group 固定模板组件代码文件
        // $this->compileFixedComponentsCode(project_path() . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, $this->addon);
        // 编译 pages.json 页面路由代码文件
        $this->uninstallPageCode(project_path() . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR);
        // 编译 pages.json 页面路由代码文件
        $this->installPageCode(project_path() . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR);
        // 编译 加载插件标题语言包
        $this->compileLocale(project_path() . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, $this->addon);

    }

    /**
     * @Notes:安装计划任务
     * @Interface installSchedule
     * @return true
     * @author: TK
     * @Time: 2024/7/17   上午8:14
     */
    public function installSchedule()
    {
        (new CoreScheduleInstallService())->installAddonSchedule($this->addon_key);
        return true;
    }

    /**
     * @Notes:刷新菜单
     * @Interface refreshMenu
     * @return true
     * @author: TK
     * @Time: 2024/7/17   上午8:13
     */
    public function refreshMenu()
    {
        (new CoreMenuService())->refreshAddonMenu($this->addon_key);
        return true;
    }

    /**
     * @Notes:执行版本更新数据库和文件
     * @Interface executeUpgrade
     * @param $install_version
     * @return array
     * @author: TK
     * @Time: 2024/7/17   上午9:53
     */
    public function executeUpgrade($install_version, $installed_version)
    {
        $addon_key = $this->addon_key;
        $dir = $this->tempPath . '/' . $addon_key . '/app/upgrade';
        if (!is_dir($dir)) {
            return [];
        }
        $sqlFiles = array();
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            $path = $dir . '/' . $file;
            if (is_file($path . '/upgrade.sql')) {
                // 如果是upgrade.sql文件，则添加到数组中
                $version = str_replace('v', '', $file);
                $version = implode('.', str_split($version));
                $result = $this->compareVersions($installed_version, $version);
                if ($result == -1) {
                    try {
                        $this->executeSql($path . '/upgrade.sql');
                        Log::write($addon_key . '---执行SQL文件SUCCESS---' . $version);
                    } catch (\Exception $e) {
                        Log::write($addon_key . '---执行SQL文件失败---' . $version . $e->getMessage());
                    }

                }
            }
            if (is_file($path . '/Upgrade.php')) {
                $version = str_replace('v', '', $file);
                $version = implode('.', str_split($version)); // 将版本号格式化为 x.x.x
                $result = $this->compareVersions($installed_version, $version);
                if ($result == -1) {
                    $path = '\\addon\\' . $addon_key . '\\app\\upgrade\\' . $file . '\\Upgrade';
                    $path = str_replace('.php', '', $path);
                    if (class_exists($path)) {
                        try {
                            (new $path())->handle();
                            Log::write($addon_key . '---执行升级方法文件SUCCESS---' . $version);
                        } catch (CommonException $e) {
                            Log::write($addon_key . '---执行升级方法文件FAIL---' . $version);
                        }
                    }
                }
            }
        }
        return $sqlFiles;
    }

    /**
     * @Notes:执行更新
     * @Interface executeSql
     * @param string $sql_file
     * @return true
     * @author: TK
     * @Time: 2024/7/17   上午8:10
     */
    public function executeSql(string $sql_file)
    {
        $sql_content = file_get_contents($sql_file);

        if (!empty($sql_content)) {
            $prefix = config('database.connections.mysql.prefix');
            $sql_data = array_filter($this->getSqlQuery($sql_content));

            if (!empty($sql_data)) {
                foreach ($sql_data as $sql) {
                    $sql = $prefix ? $this->handleSqlPrefix($sql, $prefix) : $sql;
                    try {
                        Db::query($sql);
                    } catch (\Exception $e) {
                        Log::write('执行SQL语句失败: ' . $e->getMessage());
                    }
                }
            }
        }
        return true;
    }

    public function handleSqlPrefix($sql, $prefix)
    {
        if (str_contains($sql, 'CREATE TABLE')) {
            $match_item = preg_match('/CREATE TABLE [`]?(\\w+)[`]?/is', $sql, $match_data);
        } elseif (str_contains($sql, 'ALTER TABLE')) {
            $match_item = preg_match('/ALTER TABLE [`]?(\\w+)[`]?/is', $sql, $match_data);
        } elseif (str_contains($sql, 'INSERT INTO')) {
            $match_item = preg_match('/INSERT INTO [`]?(\\w+)[`]?/is', $sql, $match_data);
        } elseif (str_contains($sql, 'DELETE FROM')) {
            $match_item = preg_match('/DELETE FROM [`]?(\\w+)[`]?/is', $sql, $match_data);
        } elseif (str_contains($sql, 'UPDATE')) {
            $match_item = preg_match('/UPDATE [`]?(\\w+)[`]?/is', $sql, $match_data);
        } elseif (str_contains($sql, 'DROP TABLE IF EXISTS')) {
            $match_item = preg_match('/DROP TABLE IF EXISTS [`]?(\\w+)[`]?/is', $sql, $match_data);
        } elseif (str_contains($sql, 'DROP TABLE')) {
            $match_item = preg_match('/DROP TABLE [`]?(\\w+)[`]?/is', $sql, $match_data);
        } else {
            $match_item = 0;
        }
        if ($match_item > 0) {
            $table_name = $match_data[1];
            $new_table_name = $prefix . $table_name;
            $sql = implode($new_table_name, explode($table_name, $sql, 2));
        }
        return $sql;
    }

    public function getSqlQuery($sql_data)
    {
        $sql_data = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $sql_data);

        $sql_data = str_replace("\r", "\n", $sql_data);
        $sql_query = [];
        $num = 0;
        $sql_arr = explode(";\n", trim($sql_data));
        unset($sql);
        foreach ($sql_arr as $sql) {
            $sql_query[$num] = '';
            $sqls = explode("\n", trim($sql));
            $sqls = array_filter($sqls);
            foreach ($sqls as $query) {
                $str1 = $query[0] ?? '';
                if ($str1 != '#' && $str1 != '-')
                    $sql_query[$num] .= $query;
            }
            $num++;
        }
        return $sql_query;
    }

    /**
     * @Notes:版本比较
     * @Interface compareVersions
     * @param $version1
     * @param $version2
     * @return int
     * @author: TK
     * @Time: 2024/7/17   上午8:11
     */
    public function compareVersions($version1, $version2)
    {
        $version1 = version_to_int($version1);
        $version2 = version_to_int($version2);
        if ($version1 < $version2) {
            return -1;
        }
        return 1;
    }


    public function installPageCode($compile_path)
    {
        $this->addon = $this->addon_key;
        if (!file_exists($this->geAddonPackagePath($this->addon) . 'uni-app-pages.php')) return;

        $uniapp_pages = require $this->geAddonPackagePath($this->addon) . 'uni-app-pages.php';

        if (empty($uniapp_pages['pages'])) {
            return;
        }

        $pages = [];
        $addon_arr = array_unique(array_merge([$this->addon], array_column((new CoreAddonService())->getInstallAddonList(), 'key')));
        foreach ($addon_arr as $addon) {
            if (!file_exists($this->geAddonPackagePath($addon) . 'uni-app-pages.php')) continue;
            $uniapp_pages = require $this->geAddonPackagePath($addon) . 'uni-app-pages.php';
            if (empty($uniapp_pages['pages'])) continue;

            $page_begin = strtoupper($addon) . '_PAGE_BEGIN';
            $page_end = strtoupper($addon) . '_PAGE_END';

            // 对0.2.0之前的版本做处理
            $uniapp_pages['pages'] = preg_replace_callback('/(.*)(\\r\\n.*\/\/ PAGE_END.*)/s', function ($match) {
                return $match[1] . (substr($match[1], -1) == ',' ? '' : ',') . $match[2];
            }, $uniapp_pages['pages']);

            $uniapp_pages['pages'] = str_replace('PAGE_BEGIN', $page_begin, $uniapp_pages['pages']);
            $uniapp_pages['pages'] = str_replace('PAGE_END', $page_end, $uniapp_pages['pages']);
            $uniapp_pages['pages'] = str_replace('{{addon_name}}', $addon, $uniapp_pages['pages']);

            $pages[] = $uniapp_pages['pages'];
        }

        $content = @file_get_contents($compile_path . "pages.json");
        $content = preg_replace_callback('/(.*\/\/ \{\{ PAGE_BEGAIN \}\})(.*)(\/\/ \{\{ PAGE_END \}\}.*)/s', function ($match) use ($pages) {
            return $match[1] . PHP_EOL . implode(PHP_EOL, $pages) . PHP_EOL . $match[3];
        }, $content);

        // 找到页面路由文件 pages.json，写入内容
        return file_put_contents($compile_path . "pages.json", $content);
    }

    /**
     * 编译 pages.json 页面路由代码文件
     * @param $compile_path
     * @return bool|int|void
     */
    public function uninstallPageCode($compile_path)
    {
        $this->addon = $this->addon_key;
        if (!file_exists($this->geAddonPackagePath($this->addon) . 'uni-app-pages.php')) return;

        $uniapp_pages = require $this->geAddonPackagePath($this->addon) . 'uni-app-pages.php';

        if (empty($uniapp_pages['pages'])) {
            return;
        }

        $pages = [];
        $addon_arr = array_diff(array_column((new CoreAddonService())->getInstallAddonList(), 'key'), [$this->addon]);

        foreach ($addon_arr as $addon) {
            if (!file_exists($this->geAddonPackagePath($addon) . 'uni-app-pages.php')) continue;
            $uniapp_pages = require $this->geAddonPackagePath($addon) . 'uni-app-pages.php';
            if (empty($uniapp_pages['pages'])) continue;

            $page_begin = strtoupper($addon) . '_PAGE_BEGIN';
            $page_end = strtoupper($addon) . '_PAGE_END';

            $uniapp_pages['pages'] = str_replace('PAGE_BEGIN', $page_begin, $uniapp_pages['pages']);
            $uniapp_pages['pages'] = str_replace('PAGE_END', $page_end, $uniapp_pages['pages']);
            $uniapp_pages['pages'] = str_replace('{{addon_name}}', $addon, $uniapp_pages['pages']);

            $pages[] = $uniapp_pages['pages'];
        }

        $content = @file_get_contents($compile_path . "pages.json");
        $content = preg_replace_callback('/(.*\/\/ \{\{ PAGE_BEGAIN \}\})(.*)(\/\/ \{\{ PAGE_END \}\}.*)/s', function ($match) use ($pages) {
            return $match[1] . PHP_EOL . implode(PHP_EOL, $pages) . PHP_EOL . $match[3];
        }, $content);
        // 找到页面路由文件 pages.json，写入内容
        return file_put_contents($compile_path . "pages.json", $content);
    }

    public function geAddonPackagePath(string $addon)
    {
        return root_path() . 'addon' . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR . 'package' . DIRECTORY_SEPARATOR;
    }
}