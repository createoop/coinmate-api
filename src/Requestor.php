<?php namespace CoinmateApi;

class Requestor
{
    public function sendRequest($path, $method, array $request = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        switch ($method)
        {
            case "post":
                curl_setopt($ch, CURLOPT_URL, $path);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request));
            break;

            case "get":
                curl_setopt($ch, CURLOPT_URL, "{$path}?".http_build_query($request));
            break;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/x-www-form-urlencoded"]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $errorCode = curl_errno($ch);

        curl_close($ch);

        if ( ! $error) {
            $result = json_decode($response, 1);
        } else {
            $result['curl_err_code'] = $errorCode;
            $result['curl_err_msg'] = $error;
        }

        return $result;
    }
}

