<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class ControllerBase extends Controller
{
    /**
     * @var array
     */
    const HEADERS = ['Access-Control-Allow-Origin' => '*'];

    /**
     * Sends a successful response.
     *
     * @param array $data
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function responseSuccess(array $data)
    {
        return new JsonResponse($data, 200, self::HEADERS);
    }

    /**
     * Sends an exception response.
     *
     * @param \Exception $e
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function responseException(\Exception $e)
    {
        return new JsonResponse(['message' => (string)$e], 500, self::HEADERS);
    }

    /**
     * Sends an error response.
     *
     * @param string $message
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function responseError(string $message)
    {
        return new JsonResponse(['message' => $message], 500, self::HEADERS);
    }
}