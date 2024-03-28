<?php

namespace pub007\dstruct\filetools;

use Prefs;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

/**
 *
 * @author OpenAI for original outline
 *
 */
class S3FileHandler {
	private $s3;
	private $bucketName;

	public function __construct(
			string $bucketName,
			string $region = null,
			string $accessKey = null,
			string $secretKey = null,
			string $cacertPath = null
		) {
		// Set up AWS credentials and S3 client
		$this->bucketName = $bucketName;

		$s3Config = [
			'version' => 'latest',
			'region' => $region, // e.g eu-west-2
			'credentials' => [
				'key' => $accessKey, // AWS access key
				'secret' => $secretKey, // AWS secret key
			]
		];

		if ($cacertPath) {
			$s3Config['http'] = [
				'verify' => true,
				'curl' => [
					CURLOPT_CAINFO => $cacertPath,
				],
			];
		}

		$this->s3 = new S3Client($s3Config);
	}

	public function uploadFile(string $fileKey, string $filePath) {
		try {
			$result = $this->s3->putObject([
				'Bucket' => $this->bucketName,
				'Key' => $fileKey,
				'SourceFile' => $filePath,
			]);

			return $result;
		} catch (AwsException $e) {
			error_log($e->getMessage());
			// Handle exception
			return false;
		}
	}

	public function getFile($fileKey) {
		try {
			$result = $this->s3->getObject([
				'Bucket' => $this->bucketName,
				'Key' => $fileKey,
			]);

			return $result;
		} catch (AwsException $e) {
			error_log($e->getMessage());
			// Handle exception
			return false;
		}
	}

	public function updateFile($fileKey, $filePath) {
		// To update a file in S3, you can upload a new version of the file with the same key
		return $this->uploadFile($fileKey, $filePath);
	}

	public function deleteFile($fileKey) {
		try {
			$result = $this->s3->deleteObject([
				'Bucket' => $this->bucketName,
				'Key' => $fileKey,
			]);

			return $result;
		} catch (AwsException $e) {
			error_log($e->getMessage());
			// Handle exception
			return false;
		}
	}
}
