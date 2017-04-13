<?php
namespace api\components;

/**
 * 全局事件响应
 *
 */
use common\models\Individual;
use Yii;
use common\lib\LogDB;
use yii\base\Exception;

class GlobalEvents
{
    private static $param;   //请求url参数
    private static $api_name;   //请求路径
    private static $app_id;   //header 里的参数
    private static $ud_id;    //header里面ud_id
    private static $user_ip;    //用户ip
    private static $server_ip;  //服务器IP
    private static $request_method ; //请求方式
    private static $user_sys;   //用户系统
    private static $user_phone; //用户手机
    public static $status;      //用户状态
    public static $err_code;    //用户code
    public static $err_message; //返回信息
    private static $star_time;  //访问开始时间

    /**
     * 该方法，用于handle，CWebApplication对象触发的onBeginRequest事件
     * @param CEvent $event
     */
    public static function onBeginRequest($event)
    {
        $app = $event->sender; /* @var $app CWebApplication */
        if (YII_ENV !== 'prod' || strpos($app->request->pathInfo, 'gii') === 0) {
            return;
        }

        /**  start 获取请求头信息 $headers  */
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if ('HTTP_' == substr($key, 0, 5)) {
                $headers[str_replace('_', '-', substr($key, 5))] = $value;
            }
        }

        if (isset($headers['X-APP-ID']) && !empty($headers['X-APP-ID'])) {
            self::$app_id = (string)$headers['X-APP-ID'];   //app_id
        }
        if (isset($headers['X-UDID']) && !empty($headers['X-UDID'])) {
            self::$ud_id = (string)$headers['X-UDID'];  //ud_id
        }
        if (isset($headers['X-USER-PHONE-BRAND']) && !empty($headers['X-USER-PHONE-BRAND'])) {
            self::$user_phone = (string)$headers['X-USER-PHONE-BRAND'];   //手机牌子
        }
        /** end 获取请求头信息 */
        if (isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT'])) {
            $agent = (string)$_SERVER['HTTP_USER_AGENT'];    //用户系统
            self::$user_sys = $agent;
        }

        if (isset($_SERVER['REQUEST_METHOD']) && !empty($_SERVER['REQUEST_METHOD'])) {
            self::$request_method = $_SERVER['REQUEST_METHOD'];   //请求方式
        }

        if (isset($_SERVER['REDIRECT_STATUS']) && !empty($_SERVER['REDIRECT_STATUS'])) {
            self::$status = $_SERVER['REDIRECT_STATUS'];   //状态值
        }

        if (!empty($_REQUEST)) {     //获取参数
            if (isset($_REQUEST['password'])) {
                unset($_REQUEST['password']);       //去除api的password
            }
            if (isset($_REQUEST['user'])) {          //去除H5的  password
                if (isset($_REQUEST['user']['password'])) {
                    unset($_REQUEST['user']['password']);
                }
            }
            self::$param = json_encode($_REQUEST);
        }



        if (Yii::$app->getRequest()->getPathInfo()) {    //获取路径
            self::$api_name = Yii::$app->getRequest()->getPathInfo();
        }

//        self::$user_ip = isset($_SERVER["HTTP_VIA"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"]; //客户端IP
        self::$user_ip = Yii::$app->request->getUserIP();//客户端IP
        self::$server_ip = $_SERVER["SERVER_ADDR"];     //服务器端IP
        self::$star_time = self::microtime_float();  //获取当前微秒数
    }



    /**
     * 该方法，用于handle，CWebApplication对象触发的onEndRequset事件
     * @param CEvent $event
     * @return type
     */
    public static function onEndRequest($event)
    {

        $app = $event->sender; /* @var $app CWebApplication */

        if (YII_ENV !== 'prod'  || strpos($app->request->pathInfo, 'gii') === 0) {
            return;
        }

        $user_id = Yii::$app->session->get('openid', '');
        //

        $token = filter_input(INPUT_SERVER, 'HTTP_X_TOKEN');
        if (!empty($token)) {
            $token = substr($token, 0, 10) . "..";
        } else {
            $token = 0;
        }

        $response = $app->response->data;
        if (is_array($response) && isset($response['code'])) {
            self::$err_code = $response['code'];
        }
        if (is_array($response) && isset($response['message'])) {
            self::$err_message = $response['message'];
        }
        $logdb = new LogDb();
        $data  = array(
            'user_id'        => $user_id,
            'user_token'     => $token,
            'app_id'         => self::$app_id,
            'ud_id'          => self::$ud_id,
            'api_name'       => self::$api_name,
            'param'          => self::$param,
            'request_method' => self::$request_method,
            'status'         => self::$status,
            'err_code'       => self::$err_code,
            'err_message'    => self::$err_message,
            'user_sys'       => self::$user_sys,
            'user_phone'     => self::$user_phone,
            'user_ip'        => self::$user_ip,
            'server_ip'      => self::$server_ip,
            'end_time'       => time(),
            'spend_time'     => self::microtime_float() - self::$star_time,
        );
        $logdb->send($data);
    }

    public static function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());

        return ((float)$usec + (float)$sec);
    }
}
