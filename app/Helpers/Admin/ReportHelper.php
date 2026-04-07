<?php

namespace App\Helpers\Admin;

class ReportHelper
{
    public static function request_check($request, $token, $nparam)
    {
        $param = '';
        $i = 0;
        foreach ($_REQUEST as $key => $val) {
            if ($key != 'PHPSESSID' and $key != 'token' and $i == 0) {
                if ($i == 0) {
                    $param .= $key . ':' . $val;
                } else {
                    $param .= $key . ':' . $val . ':';
                }
            }
            $i++;
            if ($i > $nparam) {
                break;
            }
        }

        $param = md5($param . "}D&X*G9=!j\@CG.p\)");
        if ($param == $token) {
            return true;
        } else {
            return false;
        }

        return $param;
    }

    public static function get_token($request, $nparam)
    {
        $param = '';
        $paramurl = '';
        $i = 0;
        foreach ($request as $key => $val) {
            if ($key != 'PHPSESSID' and $key != 'token' and $key != 'format') {
                if ($i == 0) {
                    $param .= $key . ':' . $val;
                } else {
                    $param .= $key . ':' . $val . ':';
                }
                $paramurl .= $key . '=' . $val . '&';
            }
            $i++;
            if ($i > $nparam) {
                break;
            }
        }
        // dd($paramurl);
        $param = md5($param . "}D&X*G9=!j\@CG.p\)");

        return $param;

    }
}
