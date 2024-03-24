<?php
namespace pub007\dstruct\filetools;

use Prefs;

/**
 *
 * @author OpenAI for original outline
 *
 */
class S3FileHandler {
	use Aws\S3\S3Client;
	use Aws\Exception\AwsException;

	private $s3;
	private $bucketName;

	public function __construct(string $bucketName, string $region = null, string $accessKey = null, string $secretKey = null) {
		// Set up AWS credentials and S3 client
		$this->bucketName = $bucketName;

		$region = $region ?? Prefs::gi()->get('s3-bucket-region');
		$accessKey = $accessKey ?? Prefs::gi()->get('s3-access-key');
		$secretKey = $secretKey ?? Prefs::gi()->get('s3-secret-key');

		$this->s3 = new S3Client([
			'version' => 'latest',
			'region' => $region, // Replace with your S3 bucket region
			'credentials' => [
				'key' => $accessKey, // AWS access key
				'secret' => $secretKey, // AWS secret key
			]
		]);
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
