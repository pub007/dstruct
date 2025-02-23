<?php

namespace pub007\dstruct\filetools;

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
	
	/**
	 * Upload a file to S3
	 * 
	 * If <var>$filePath</var> is set, the file will be uploaded from the file system.
	 * if <var>$fileContent</var> is set, the file content should be passed as a string,
	 * for example from file_get_contents().
	 * 
	 * @param string $fileKey Path and Name of the file on S3
	 * @param string $filePath Path to the file to upload
	 * @param string $fileContent Content of the file to upload
	 * @return \Aws\Result|boolean
	 */
	public function uploadFile(string $fileKey, string $filePath = '', string $fileContent = null)
	{
		try {
			if ($fileContent) {
				$result = $this->s3->putObject([
					'Bucket' => $this->bucketName,
					'Key' => $fileKey,
					'Body' => $fileContent,
                ]);

                return $result;
            } elseif ($filePath) {
				$result = $this->s3->putObject([
					'Bucket' => $this->bucketName,
					'Key' => $fileKey,
					'SourceFile' => $filePath,
				]);
            }

			return $result;
		} catch (AwsException $e) {
			error_log($e->getMessage());
			// Handle exception
			return false;
		}
	}
	
	/**
	 * Get a file from S3
	 * @param string $fileKey
	 * @return \Aws\Result|boolean
	 */
	public function getFile(string $fileKey) {
		try {
			$result = $this->s3->getObject([
				'Bucket' => $this->bucketName,
				'Key' => $fileKey,
			]);

			return $result;
		} catch (AwsException $e) {
			error_log($e->getMessage());
				// Handle exceptionn
			return false;
		}
	}
	
	/**
     * Update a file in S3
     * @param string $fileKey
     * @param string $filePath
     * @return \Aws\Result|boolean
     */
	public function updateFile(string $fileKey, string $filePath) {
		// To update a file in S3, you can upload a new version of the file with the same key
		return $this->uploadFile($fileKey, $filePath);
	}
	
	/**
     * Delete a file from S3
     * @param string $fileKey
     * @return \Aws\Result!boolean
     */
	public function deleteFile(string $fileKey) {
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
