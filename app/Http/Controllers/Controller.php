<?php

namespace App\Http\Controllers;

use Endroid\QrCode\QrCode;
use App\Http\Response\Status;
use App\Http\Response\Response;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public static function response($data = [], $responseCode = null, $headers = [])
    {
        return self::handleResponse($data, $responseCode, $headers);
    }

    public static function responseCode($responseCode = null, $headers = [])
    {
        return self::handleResponse(null, $responseCode, $headers);
    }

    protected static function handleResponse($data, $responseCode, $headers)
    {
        $responseCode == null && $responseCode = Status::SUCCESS;

        isset($data) && $response['data'] = $data;

        isset($responseCode[Status::STATE]) && $response['state'] = $responseCode[Status::STATE];
        isset($responseCode[Status::MESSAGE]) && $response['message'] = $responseCode[Status::MESSAGE];

        return new Response($response, $responseCode[Status::CODE], $headers);
    }

    public static function chooseIn(array $array)
    {
        return 'please choose in [' . implode(',', $array) . ']';
    }

    public static function createQrCode($string)
    {
        $qrCode = new QrCode();

        $qrCode
            ->setText($string)
            ->setSize(200)
            ->setPadding(3)
            ->setErrorCorrection('high')
            ->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0])
            ->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0])
            ->setImageType(QrCode::IMAGE_TYPE_PNG);

        return $qrCode->get();
    }

    public static function createBase64Image($string)
    {
        return 'data:image/png;base64,' . base64_encode($string);
    }
}
