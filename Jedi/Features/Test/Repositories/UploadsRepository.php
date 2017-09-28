<?php
namespace Jedi\Features\Documents\Repositories;

use Aws\S3\Exception\S3Exception;
use Jedi\Repositories\EloquentRepository;
use Illuminate\Database\Eloquent\Model;
use Jedi\Repositories\ValidatorInterface;

class UploadsRepository extends EloquentRepository implements UploadsInterface
{
    protected $validator;

    protected $rules = [
        'file' => 'max:502400',
    ];

    protected $messages = [
        'max' => 'File exceeded to maximum file size allowed'
    ];
    
    

    public function __construct(Model $model, ValidatorInterface $validator)
    {
        parent::__construct($model);

        $this->validator = $validator;
    }
    
    public function upload($s3, $uploads, array $attributes)
    {
    	$uploaded = null;
    	$s3_data  = null;
    
    	try {    		
            if (is_a($uploads, 'Symfony\Component\HttpFoundation\File\UploadedFile')) {
                $file_size = $uploads->getSize();
                $mime_type = $uploads->getMimeType();

                $key = date('Y').'/'.date('F').'/'.date('d').'/'.$attributes['filename'];

                $s3_data = array(
                    'Bucket'      => $this->get_bucket_name(),
                    'Key'         => $key,
                    'SourceFile'  => $uploads->getRealPath(),
                    'ACL'         => \Aws\S3\Enum\CannedAcl::AUTHENTICATED_READ,
                    'ContentType' => $attributes['mime_type']
                );

                $results = $s3->putObject($s3_data);

                $s3->waitUntilObjectExists(array(
                    'Bucket' => $this->get_bucket_name(),
                    'Key'    => $key
                ));

                if ($results) {
                    unset($s3_data);

                    $uploaded = ['status' => true, 'data' => [
                                 'filename'   => $attributes['filename'],
                                 'filesize'   => $file_size,
                                 'extension'  => $attributes['extension'],
                                 'mime_type'  => $mime_type,
                                 'key'        => $key,
                                 'original_filename' => $attributes['original_filename'],
                                 'raw'        => $uploads
                    ]];

                    $this->log([
                        [
                            'action' => SYSTEM_LOGS_ACTION_INSERT,
                            'source' => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                            'new_value' => json_encode(['uploads' => [
                                'filename'   => $attributes['filename'],
                                'filesize'   => $file_size,
                                'extension'  => $attributes['extension'],
                                'mime_type'  => $mime_type,
                                'key'        => $key,
                                'original_filename' => $attributes['original_filename'],
                                'action_by'  => ''
                            ]])
                        ]
                    ]);
                }
            }
    	} catch (\Exception $e) {
            $uploaded = ['status' => false, 'message' => $this->parse_sql_error_message($e->getMessage())];

            $this->log([
                [
                    'action' => SYSTEM_LOGS_ACTION_ERROR,
                    'source' => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                    'new_value' => json_encode(['uploads' => [
                        'error_msg' => $e->getMessage()
//                        'error_msg' => $this->parse_sql_error_message($e->getMessage())
                    ]])
                ]
            ]);
    	}
    
    	return $uploaded;
    }

    public function copy_image($s3, array $images)
    {
        try {
            foreach ($images as $image) {
                $key = date('Y').'/'.date('F').'/'.date('d').'/images/'.$image;

                $s3->putObject([
                'Bucket'      => $this->get_bucket_name(),
                    'Key'         => $key,
                    'SourceFile'  => config('upload.pos_folder') . '/' .$image,
                    'ACL'         => \Aws\S3\Enum\CannedAcl::AUTHENTICATED_READ
                ]);

                $s3->waitUntilObjectExists(array(
                    'Bucket' => $this->get_bucket_name(),
                    'Key'    => $key
                ));

                $copy = ['status' => true, 'message' => 'Image has been copied to server'];
            }
        } catch (\S3Exception $s3e) {
            $copy = ['status' => false, 'message' => $s3e->getMessage()];
            $this->log([
                [
                    'action' => SYSTEM_LOGS_ACTION_ERROR,
                    'source' => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                    'new_value' => json_encode(['uploads' => [
                        'error_msg' => $s3e->getMessage()
                    ]])
                ]
            ]);
        } catch (\Exception $e) {
            $copy = ['status' => false, 'message' => $e->getMessage()];
            $this->log([
                [
                    'action' => SYSTEM_LOGS_ACTION_ERROR,
                    'source' => SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED,
                    'new_value' => json_encode(['uploads' => [
                        'error_msg' => $e->getMessage()
                    ]])
                ]
            ]);
        }

        return $copy;
    }
    
    public function getUrl($s3, $file_name, $expiration = false)
    {
    	$expired_at = '';
    
    	if ($expiration) {
            $expired_at = '30 minutes';
    	}
    
    	return $s3->getObjectUrl($this->get_bucket_name(), $file_name, $expired_at);
    }
    
    public function download($s3, $key, $filename)
    {
    	$response = ['status' => false, 'message' => 'Invalid request'];

        try {
            $filename = config('upload.pos_folder') . '/' . $filename;
            $f = fopen($filename, 'w+');

            $object = $s3->getObject([
                'Bucket' => $this->get_bucket_name(),
                'Key'    => $key,
                'SaveAs' => $f
            ]);

            if ($response) {
                $response = ['status' => true, 'message' => '', 'data' => [
                    'filename'       => $filename,
                    'request_id'     => $object['RequestId'],
                    'content_length' => $object['ContentLength']
                ]];
            }
        } catch (\S3Exception $e) {
            $response['message'] = $e->getMessage();
        }


    	return $response;
    }
    
   
    public function validate_inputs (array $inputs)
    {
        $this->validator->with($inputs);
        $this->validator->rules($this->rules);
        $this->validator->messages($this->messages);

        if ($this->validator->passes()) {
            $status  = true;
            $errors  = null;
        } else {
            $status = false;
            $errors = $this->validator->errors()->all();
        }

        return ['status' => $status , 'errors' => $errors];
    }

    private function get_bucket_name()
    {
    	return config('upload.bucket_name');
    }
}