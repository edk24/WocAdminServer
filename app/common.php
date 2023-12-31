<?php
// 应用公共文件

use app\common\enums\ApiCodeEnum;
use think\Response;

/**
 * 响应数据
 *
 * @param mixed $data
 * @param ApiCodeEnum $code
 * @param string|null $customMessage
 * @return Response
 */
function resp_data(mixed $data, ApiCodeEnum $code = ApiCodeEnum::OK, ?string $customMessage = null): Response
{
    return json()->data([
        'code'      => $code->value,
        'msg'       => $customMessage ?? $code->message(),
        'data'      => $data,
    ]);
}

/**
 * 响应成功
 *
 * @param mixed $data
 * @param string|null $customMessage
 * @param ApiCodeEnum $code
 * @return Response
 */
function resp_success(mixed $data, ?string $customMessage, ApiCodeEnum $code = ApiCodeEnum::OK): Response
{
    return json()->data([
        'code'      => $code->value,
        'msg'       => $customMessage ?? $code->message(),
        'data'      => $data,
    ]);
}


/**
 * 响应失败
 *
 * @param string|null $customMessage
 * @param ApiCode $code
 * @param mixed $data
 * @return Response
 */
function resp_fail(?string $customMessage = null, ApiCodeEnum $code = ApiCodeEnum::FAIL, mixed $data = null): Response
{
    return json()->data([
        'code'      => $code->value,
        'msg'       => $customMessage ?? $code->message(),
        'data'      => $data,
    ]);
}


/**
 * 首字母头像
 * @param $text
 * @return string
 */
function letter_avatar($text)
{
    $total = unpack('L', hash('adler32', $text, true))[1];
    $hue = $total % 360;
    list($r, $g, $b) = hsv2rgb($hue / 360, 0.3, 0.9);

    $bg = "rgb({$r},{$g},{$b})";
    $color = "#ffffff";
    $first = mb_strtoupper(mb_substr($text, 0, 1));
    $src = base64_encode('<svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="100" width="100"><rect fill="' . $bg . '" x="0" y="0" width="100" height="100"></rect><text x="50" y="50" font-size="50" text-copy="fast" fill="' . $color . '" text-anchor="middle" text-rights="admin" dominant-baseline="central">' . $first . '</text></svg>');
    $value = 'data:image/svg+xml;base64,' . $src;
    return $value;
}


function hsv2rgb($h, $s, $v)
{
    $r = $g = $b = 0;

    $i = floor($h * 6);
    $f = $h * 6 - $i;
    $p = $v * (1 - $s);
    $q = $v * (1 - $f * $s);
    $t = $v * (1 - (1 - $f) * $s);

    switch ($i % 6) {
        case 0:
            $r = $v;
            $g = $t;
            $b = $p;
            break;
        case 1:
            $r = $q;
            $g = $v;
            $b = $p;
            break;
        case 2:
            $r = $p;
            $g = $v;
            $b = $t;
            break;
        case 3:
            $r = $p;
            $g = $q;
            $b = $v;
            break;
        case 4:
            $r = $t;
            $g = $p;
            $b = $v;
            break;
        case 5:
            $r = $v;
            $g = $p;
            $b = $q;
            break;
    }

    return [
        floor($r * 255),
        floor($g * 255),
        floor($b * 255)
    ];
}
