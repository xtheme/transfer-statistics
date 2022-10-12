<?php

namespace xtheme\TransferStatistics;

use Closure;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

/**
 * TransferStatistics v2 应用监控系统
 */
class TransferStatisticsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('transfer-statistics.enable')) {
            return $next($request);
        }

        $startTime = LARAVEL_START;            // 开始时间
        $project = config('app.name');         // 应用名
        $ip = $request->ip();                  // 请求IP
        $transfer = $request->getUri();        // 调用

        $response = $next($request);

        $finishTime = microtime(true);         // 结束时间
        $costTime = $finishTime - $startTime;  // 运行时长

        $code = $response->status();           // 状态码
        $success = $code < 400;                // 是否成功

        // 详细信息，自定义设置
        $details = [
            'time'     => date('Y-m-d H:i:s.', (int) $startTime),   // 请求时间（包含毫秒时间）
            'run_time' => $costTime,                                // 运行时长
            'request'  => $request->input(),                        // 请求参数
        ];

        // 执行上报
        try {
            // 数据打包 多条 换行 隔开
            $data = json_encode([
                    'time'     => date('Y-m-d H:i:s.', (int) $startTime),
                    'project'  => $project,
                    'ip'       => $ip,
                    'transfer' => $transfer,
                    'costTime' => $costTime,
                    'success'  => $success ? 1 : 0,
                    'code'     => $code,
                    'details'  => json_encode($details, 320),
                ], 320) . "\n";

            $client = new Client(['verify' => false]);

            $client->post(
                // 上报地址
                config('transfer-statistics.host') . '/report/statistic/transfer',
                [
                    'headers'     => [
                        // 上报认证，不设置默认为当前年份的md5值
                        'authorization' => md5(date('Y')),
                    ],
                    'form_params' => [
                        // 上报数据
                        'transfer' => $data,
                    ],
                ]
            );
        } catch (\Throwable $th) {
            //throw $th;
        }

        return $response;
    }
}
