<?php

/*
|--------------------------------------------------------------------------
| KONFIGURASI FONNTE
|--------------------------------------------------------------------------
*/

$fonnte_token = "eqvCQyYaZwWgLLaDG2Cc";


/*
|--------------------------------------------------------------------------
| FUNGSI KIRIM WHATSAPP
|--------------------------------------------------------------------------
*/

function kirimWhatsApp($nomor, $pesan)
{
    global $fonnte_token;

    if (
        empty($fonnte_token) ||
        $fonnte_token === "MASUKKAN_TOKEN_FONNTE_KAMU"
    ) {
        return [
            "success" => false,
            "message" => "Token Fonnte belum diisi.",
            "response" => null,
            "http_code" => 0
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | NORMALISASI NOMOR
    |--------------------------------------------------------------------------
    */

    $nomor = trim($nomor);

    $nomor = preg_replace(
        "/[^0-9]/",
        "",
        $nomor
    );


    if (substr($nomor, 0, 1) === "0") {

        $nomor =
            "62" . substr($nomor, 1);

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDASI NOMOR
    |--------------------------------------------------------------------------
    */

    if (
        !preg_match(
            "/^62[0-9]{9,15}$/",
            $nomor
        )
    ) {

        return [
            "success" => false,
            "message" => "Nomor WhatsApp tidak valid.",
            "response" => null,
            "http_code" => 0
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | URL FONNTE
    |--------------------------------------------------------------------------
    */

    $url =
        "https://api.fonnte.com/send";


    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */

    $data = [
        "target" => $nomor,
        "message" => $pesan
    ];


    /*
    |--------------------------------------------------------------------------
    | CURL
    |--------------------------------------------------------------------------
    */

    $ch = curl_init();


    curl_setopt_array(
        $ch,
        [
            CURLOPT_URL => $url,

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_POST => true,

            CURLOPT_POSTFIELDS =>
                http_build_query($data),

            CURLOPT_HTTPHEADER => [
                "Authorization: " .
                $fonnte_token
            ],

            CURLOPT_TIMEOUT => 30,

            CURLOPT_CONNECTTIMEOUT => 10
        ]
    );


    $response =
        curl_exec($ch);


    $httpCode =
        curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );


    $curlError =
        curl_error($ch);


    curl_close($ch);


    /*
    |--------------------------------------------------------------------------
    | CURL ERROR
    |--------------------------------------------------------------------------
    */

    if ($response === false) {

        return [
            "success" => false,

            "message" =>
                "Koneksi ke Fonnte gagal: " .
                $curlError,

            "response" => null,

            "http_code" => $httpCode
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

    $json =
        json_decode(
            $response,
            true
        );


    if (
        is_array($json) &&
        isset($json["status"]) &&
        $json["status"] === true
    ) {

        return [
            "success" => true,

            "message" =>
                "WhatsApp berhasil dikirim.",

            "response" => $response,

            "http_code" => $httpCode
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | GAGAL
    |--------------------------------------------------------------------------
    */

    $alasan =
        "WhatsApp gagal dikirim.";


    if (
        is_array($json) &&
        isset($json["reason"])
    ) {

        $alasan =
            $json["reason"];
    }


    return [
        "success" => false,

        "message" => $alasan,

        "response" => $response,

        "http_code" => $httpCode
    ];
}