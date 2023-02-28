<?php

namespace Ozden;

use Aws;
use GuzzleHttp;

class Converter
{

    public $region;
    public $bucket;
    public $prefix;
    public $awsCredentials;
    public $awsS3;
    public $checkExtensions;
    public $quality;
    public $deleteOld;
    public $suitableFiles = [];

    public function connectAws(array $credentials)
    {
        try {
            $this->awsCredentials = new Aws\Credentials\Credentials($credentials['key'], $credentials['secret']);
            $this->awsS3 = new Aws\S3\S3Client([
                'version' => 'latest',
                'region' => $this->region,
                'credentials' => $this->awsCredentials
            ]);
        } catch (Aws\S3\Exception\S3Exception $e) {
            echo $e->getMessage();
        }

    }

    public function setRegion(string $region)
    {
        $this->region = $region;
    }

    public function setBucket(string $bucket)
    {
        $this->bucket = $bucket;
    }

    protected function getAwsList()
    {
        try {
            $objects = $this->awsS3->getIterator('ListObjects', array(
                'Bucket' => $this->bucket,
                'Prefix' => $this->prefix
            ));

            foreach ($objects as $object) {
                $this->checkExtension($object['Key']);
            }
        } catch (Aws\S3\Exception\S3Exception $e) {
            echo $e->getMessage();
        }
    }

    public function checkExtension($file)
    {
        $parts = pathinfo($file);
        if (in_array($parts['extension'], $this->checkExtensions)) {
            $this->suitableFiles[] = $file;
        }
    }

    public function downloadFile($file)
    {
        try {
            $fullAwsPath = 'https://' . $this->bucket . '.s3.' . $this->region . '.amazonaws.com/' . $file;
            $parts = pathinfo($file);
            $client = new GuzzleHttp\Client();
            $down = $client->get($fullAwsPath, ['sink' => $parts['basename']]);
            if ($down->getReasonPhrase() == "OK") {
                return $this->convert($file);
            }
        } catch (GuzzleHttp\Exception\RequestException $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function convert($file)
    {
        $parts = pathinfo($file);

        if ($parts['extension'] == 'png') {
            $downloaded = imagecreatefrompng($parts['basename']);
        } elseif ($parts['extension'] == 'jpg' || $parts['extension'] == 'jpeg') {
            $downloaded = imagecreatefromjpeg($parts['basename']);
        }

        $newWebpName = $parts['filename'] . ".webp";
        $w = imagesx($downloaded);
        $h = imagesy($downloaded);
        $newWebpCanvas = imagecreatetruecolor($w, $h);
        imagecopy($newWebpCanvas, $downloaded, 0, 0, 0, 0, $w, $h);
        imagewebp($newWebpCanvas, $newWebpName, $this->quality);
        imagedestroy($downloaded);

        $upload = $this->uploadAws($parts['dirname'], $newWebpName);

        if ($upload === true) {
            @unlink($newWebpName);
            @unlink($parts['basename']);
            if ($this->deleteOld === true) {
                $this->deleteImageOnAws($file);
            }
            return [$file => $parts['dirname'] . "/" . $newWebpName];
        } else {
            return [$file => false];
        }
    }

    public function uploadAws($directoryAws, $newImage): bool
    {
        try {
            $uploadWebpOnAws = $this->awsS3->putObject([
                'Bucket' => $this->bucket,
                'Key' => $directoryAws . "/" . $newImage,
                'Body' => fopen($newImage, 'r'),
                'ACL' => 'public-read',
            ]);
            return ($uploadWebpOnAws['@metadata']['statusCode'] == 200) ? true : false;
        } catch (Aws\S3\Exception\S3Exception $e) {
            echo $e->getMessage();
            return false;
        }

    }

    public function deleteImageOnAws($file): bool
    {
        try {
            $deleteOnAws = $this->awsS3->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $file,
            ]);
            return ($deleteOnAws['@metadata']['statusCode'] == 200) ? true : false;
        } catch (Aws\S3\Exception\S3Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    public function start(string $prefix = '', array $extensions = ['jpg', 'jpeg', 'png'], int $quality = 70, bool $deleteOld = true)
    {
        $this->checkExtensions = $extensions;
        $this->prefix = $prefix;
        $this->quality = $quality;
        $this->deleteOld = $deleteOld;

        $this->getAwsList();
        $resultStack = [];

        if (count($this->suitableFiles) > 0) {
            foreach ($this->suitableFiles as $file) {
                $resultStack[] = $this->downloadFile($file);
            }
            return $resultStack;
        } else {
            return false;
        }
    }


}

?>