<?php
declare (strict_types=1);

namespace addon\tk_upgrade\app\command\Addon;

use app\model\addon\Addon;
use app\service\core\addon\CoreAddonInstallService;
use app\service\core\addon\CoreAddonService;
use Exception;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;

class Install extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('addon:installall')
            ->setDescription('addon install all command');
    }
    protected function execute(Input $input, Output $output)
    {
        $addon_path = root_path() . DIRECTORY_SEPARATOR . 'addon';
        $files = get_files_by_dir($addon_path);
        if (!empty($files)) {
            foreach ($files as $path) {
                $data = ( new CoreAddonService() )->getAddonConfig($path);
                if (isset($data[ 'key' ])) {
                    $addonInfo=(new Addon)->where(['key'=>$data['key'],'status'=>1])->findOrEmpty();
                    if($addonInfo->isEmpty()){
                        $install_service = ( new CoreAddonInstallService($data[ 'key' ]) );
                        $install_service->installCheck();
                        $install_service->install();
                    }
                }
            }
        }
        $output->writeln('========= install addon all success =======');
    }
}
