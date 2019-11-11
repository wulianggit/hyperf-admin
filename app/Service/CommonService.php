<?php
/**
 * Created by PhpStorm.
 * User: derek
 * Date: 2019/11/10
 * Time: 22:07
 */

namespace App\Service;


use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\SysConfig;
use App\Model\SysOss;
use App\Service\Formatter\SysOssFormatter;
use Hyperf\DbConnection\Db;

class CommonService extends Service
{

    /**
     * 返回日志管理列表
     * @param int $pageSize
     * @param int $currPage
     * @return array
     */
    public function getSysLogList(int $pageSize = 10, int $currPage = 1): array
    {
        $totalCount = SysOss::query()->count();

        if ($totalCount > 0) {
            $totalPage = ceil($totalCount / $pageSize);
        } else {
            $totalPage = 0;
        }

        if ($currPage <= 0 || $currPage > $totalPage) {
            $currPage = 1;
        }

        $startCount = ($currPage - 1) * $pageSize;

        $where = " 1=1 ";

        $sysOsss = Db::select("SELECT * FROM sys_oss a JOIN (select id from sys_oss order by id desc limit " . $startCount . ", " . $pageSize . ") b ON a.id = b.id where " . $where . " order by b.id desc;");

        if (!empty($sysOsss)) {
            $sysOsss = SysOssFormatter::instance()->arrayFormat($sysOsss);
        }

        $result = [
            'totalCount' => $totalCount,
            'pageSize' => $pageSize,
            'totalPage' => $totalPage,
            'currPage' => $currPage,
            'list' => $sysOsss
        ];
        return $result;
    }

    /**
     * oss删除
     * @param array $params
     * @return int
     */
    public function sysOssDelete(array $params)
    {
        return Db::table('sys_oss')->whereIn("id", $params)->delete();
    }

    /**
     * 获取oss的配置
     */
    public function sysOssConfig()
    {
        try {
            $result = SysConfig::query()->where("param_key", 'CLOUD_STORAGE_CONFIG_KEY')->first();
            return $result;
        } catch (\Exception $e) {
            throw  new BusinessException(ErrorCode::NOTE_NOT_EXIST);
        }
    }

    /**
     * 保存oss配置
     * @param array $params
     * @return array
     */
    public function sysOssSaveConfig(array $params)
    {
        $isResult = SysConfig::query()->where("param_key", 'CLOUD_STORAGE_CONFIG_KEY')->first();
        if (empty($isResult)) {
            $result = SysConfig::query()->create([
                'param_key' => 'CLOUD_STORAGE_CONFIG_KEY',
                'param_value' => json_encode($params, true),
                'status' => 0,
                'remark' => '云存储配置信息'
            ]);
        } else {
            $result = SysConfig::query()->where("param_key", 'CLOUD_STORAGE_CONFIG_KEY')->update([
                'param_key' => 'CLOUD_STORAGE_CONFIG_KEY',
                'param_value' => json_encode($params, true),
                'status' => 0,
                'remark' => '云存储配置信息'
            ]);
        }
        return $result;
    }

}