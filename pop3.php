<?php 
/*
        $email = $_GET['email'];
        $password = $_GET['password'];
        $command_url = $_GET['comand_url'];
        $port = $_GET['port'];

        $email = 'AurikaFomina1997@mail.ua';
        $password = '7bwlHGHxh';
        $command_url = 'pop3s://pop.mail.ru/5';
        $port = '995';
*/
    list($script, $email,$password,$command_url,$port) = $argv;
    // create curl resource 
    $curl = curl_init(); 

    if($curl) {
        /* Set username and password */ 
        curl_setopt($curl, CURLOPT_USERNAME, $email);
        curl_setopt($curl, CURLOPT_PASSWORD, $password);

        //curl_setopt($curl, CURLOPT_URL, "pop3s://pop.qq.com/1");
        //curl_setopt($curl, CURLOPT_URL, "pop3://pop.mail.ua/");
        //curl_setopt($curl, CURLOPT_PORT, 110);
        //curl_setopt($curl, CURLOPT_URL, "pop3s://pop.mail.ru/5");
        curl_setopt($curl, CURLOPT_URL, $command_url);
        curl_setopt($curl, CURLOPT_PORT, $port);

        curl_setopt($curl, CURLOPT_USE_SSL,CURLUSESSL_ALL);

        //curl_setopt($curl, CURLOPT_CAINFO, "./certificate.pem");

        //curl_setopt($curl, CURLOPT_VERBOSE, true);

        //return the transfer as a string 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        // $output contains the output string 
        $output = curl_exec($curl);
    }

    curl_close($curl); 
    echo $output;

