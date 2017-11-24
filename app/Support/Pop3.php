<?php

namespace App\Support;

use App\Support\Util;

/**
 *
 */
class Pop3
{
    public static function getAppleEmail($email, $password, $content_id = '')
    {

        Util::log('$email', $password);
        Util::log('$aaa', $email);

        $curl = curl_init();

        if ($curl) {
            /* Set username and password */
            curl_setopt($curl, CURLOPT_USERNAME, "AurikaFomina1997@mail.ua");
            curl_setopt($curl, CURLOPT_PASSWORD, "7bwlHGHxh");

            //curl_setopt($curl, CURLOPT_URL, "pop3s://pop.qq.com/1");
            //curl_setopt($curl, CURLOPT_URL, "pop3://pop.mail.ua/");
            //curl_setopt($curl, CURLOPT_PORT, 110);
            //curl_setopt($curl, CURLOPT_URL, "pop3s://pop.mail.ru/5");
            curl_setopt($curl, CURLOPT_URL, "pop3s://pop.mail.ru/");
            curl_setopt($curl, CURLOPT_PORT, 995);

            curl_setopt($curl, CURLOPT_USE_SSL, CURLUSESSL_ALL);

            //curl_setopt($curl, CURLOPT_CAINFO, "./certificate.pem");

            //curl_setopt($curl, CURLOPT_VERBOSE, true);

            //return the transfer as a string
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

            // $output contains the output string
            $output = curl_exec($curl);
        }
        Util::log('$outputaaa', $output);
        curl_close($curl);
        return $output;
    }
    // 获取苹果邮箱内容
    public static function getAppleEmail2($email, $password, $content_id = '')
    {
        list($username, $email_host) = explode('@', $email);
        Util::log($email_host, $username);

        // * 获取请求地址配置信息
        $port = '995';
        switch ($email_host) {
            case 'qq.com':
                $comand_url = 'pop3s://pop.qq.com/' . $content_id;
                break;
            case 'mail.ua':
            case 'mail.ru':
                $comand_url = 'pop3s://pop.mail.ru/' . $content_id;
                break;
            default:
                return false;
                break;
        }
        Util::log('comand_url', $comand_url);
        Util::log('email', $email);
        Util::log('password', $password);
        $curl = curl_init();
        /* Set username and password */
        curl_setopt($curl, CURLOPT_USERNAME, $email);
        curl_setopt($curl, CURLOPT_PASSWORD, $password);

        curl_setopt($curl, CURLOPT_URL, $comand_url);
        // curl_setopt($curl, CURLOPT_URL, "pop3s://pop.qq.com");
        curl_setopt($curl, CURLOPT_PORT, $port);

        curl_setopt($curl, CURLOPT_USE_SSL, CURLUSESSL_ALL);

        //curl_setopt($curl, CURLOPT_CAINFO, "./certificate.pem");

        //curl_setopt($curl, CURLOPT_VERBOSE, true);

        //return the transfer as a string
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        // $output contains the output string
        $output = curl_exec($curl);
        Util::log('output', $output);
        curl_close($curl);

        return $output;
    }

}
