<?php

declare(strict_types=1);

namespace app\common\validate;

use think\exception\HttpResponseException;
use think\Validate;

class BaseValidate extends Validate
{
    public string $method = 'GET';

    /**
     * 设置请求方式
     */
    public function post()
    {
        if (!$this->request->isPost()) {
            throw new HttpResponseException(resp_fail('请求方式错误，请使用 POST 请求'));
        }
        $this->method = 'POST';

        return $this;
    }

    /**
     * 设置请求方式
     */
    public function get()
    {
        if (!$this->request->isGet()) {
            throw new HttpResponseException(resp_fail('请求方式错误，请使用 GET 请求'));
        }
        $this->method = 'GET';


        return $this;
    }

    /**
     * 设置请求方式
     */
    public function delete()
    {
        if (!$this->request->isDelete()) {
            throw new HttpResponseException(resp_fail('请求方式错误，请使用 DELETE 请求'));
        }
        $this->method = 'DELETE';


        return $this;
    }

    /**
     * 设置请求方式
     */
    public function put()
    {
        if (!$this->request->isPut()) {
            throw new HttpResponseException(resp_fail('请求方式错误，请使用 PUT 请求'));
        }
        $this->method = 'PUT';


        return $this;
    }

    /**
     * 切面验证接收到的参数
     */
    public function goCheck($scene = null, array $validateData = []): array
    {
        //接收参数
        switch ($this->method) {
            case 'GET':
                $params = request()->get();
                break;
            case 'POST':
                $params = request()->post();
                break;
            case 'PUT':
                $params = request()->put();
                break;
            case 'DELETE':
                $params = request()->delete();
                break;
            default:
                throw new HttpResponseException(resp_fail('不支持该请求类型'));
        }

        //合并验证参数
        $params = array_merge($params, $validateData);

        //场景
        if ($scene) {
            $result = $this->scene($scene)->check($params);
        } else {
            $result = $this->check($params);
        }

        if (!$result) {
            $exception = is_array($this->error) ? implode(';', $this->error) : $this->error;
            throw new HttpResponseException(resp_fail($exception));
        }

        return $params;
    }
}
