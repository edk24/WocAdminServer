<?php

namespace library\jwt;

/**
 * PHP JWT 实现
 */
class Jwt
{
    private $header = array(
        'alg' => 'HS256', //生成signature的算法
        'typ' => 'JWT'    //类型
    );

    private $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * 获取jwt token
     * @param JwtPayload $payload jwt载荷
     * @return string 返回 Token
     */
    public function getToken(JwtPayload $payload): string
    {
        $base64header = self::base64UrlEncode(json_encode($this->header, JSON_UNESCAPED_UNICODE));
        $base64payload = self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));
        $token = $base64header . '.' . $base64payload . '.' . self::signature($base64header . '.' . $base64payload, $this->key, $this->header['alg']);
        return $token;
    }

    /**
     * 验证token是否有效, 默认验证exp,nbf,iat时间
     * 
     * @param string $Token 需要验证的token
     * @return array 返回载荷
     */
    public function verifyToken(string $token): array
    {
        $tokens = explode('.', $token);
        if (count($tokens) != 3) {
            throw new JwtException('Token 不合法');
        }

        list($base64header, $base64payload, $sign) = $tokens;

        // 获取jwt算法
        $base64decodeheader = json_decode(self::base64UrlDecode($base64header), JSON_OBJECT_AS_ARRAY);
        if (empty($base64decodeheader['alg'])) {
            throw new JwtException('Token 不合法');
        }

        // 签名验证
        if (self::signature($base64header . '.' . $base64payload, $this->key, $base64decodeheader['alg']) !== $sign) {
            throw new JwtException('Token 不正确');
        }

        $payload = json_decode(self::base64UrlDecode($base64payload), JSON_OBJECT_AS_ARRAY);

        // 签发时间大于当前服务器时间验证失败
        if (isset($payload['iat']) && $payload['iat'] > time()) {
            throw new JwtException('Token 已过期');
        }

        // 过期时间小于当前服务器时间验证失败
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new JwtException('Token 已过期');
        }

        // 该nbf时间之前不接收处理该Token
        if (isset($payload['nbf']) && $payload['nbf'] > time()) {
            throw new JwtException('Token 还未生效');
        }

        return $payload;
    }











    /**
     * base64UrlEncode   https://jwt.io/  中base64UrlEncode编码实现
     * @param string $input 需要编码的字符串
     * @return string
     */
    private static function base64UrlEncode(string $input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    /**
     * base64UrlEncode  https://jwt.io/  中base64UrlEncode解码实现
     * @param string $input 需要解码的字符串
     * @return bool|string
     */
    private static function base64UrlDecode(string $input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $addlen = 4 - $remainder;
            $input .= str_repeat('=', $addlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * HMACSHA256签名   https://jwt.io/  中HMACSHA256签名实现
     * @param string $input 为base64UrlEncode(header).".".base64UrlEncode(payload)
     * @param string $key
     * @param string $alg   算法方式
     * @return mixed
     */
    private static function signature(string $input, string $key, string $alg = 'HS256')
    {
        $alg_config = array(
            'HS256' => 'sha256'
        );
        return self::base64UrlEncode(hash_hmac($alg_config[$alg], $input, $key, true));
    }
}
