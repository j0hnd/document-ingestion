<?php
namespace Jedi\Features\Sites\Repositories;

use Jedi\Repositories\EloquentRepository;
use Illuminate\Database\Eloquent\Model;

use Jedi\Repositories\ValidatorInterface;

use Jedi\Features\Documents\Repositories\UploadsRepository;
use Jedi\Features\Documents\Models\DocumentsModel;

use Hash, DB, AWS;


class InputTemplatesRepository extends EloquentRepository implements InputTemplatesInterface
{
    protected $validator;
    protected $uploadRepo;


    public function __construct(Model $model, ValidatorInterface $validator)
    {
        parent::__construct($model);

        $this->uploadRepo = new UploadsRepository(new DocumentsModel(), $validator);

        $this->validator = $validator;
    }

    public function read_input_template($site_id)
    {
        $response = ['status' => false, 'message' => 'Invalid request'];

        try {

            $input_template_obj = DB::table('input_templates as i')
                ->select(DB::raw('i.filename, i.location'))
                ->join('sites as s', 's.id', '=', 'i.site_id')
                ->where('s.id', $site_id)
                ->where('s.is_active', 1);

            if ($input_template_obj->count()) {
                $input_templates = $input_template_obj->first();

                $url = $this->uploadRepo->getUrl(AWS::get('s3'), $input_templates->location);

                if ($input_templates->filename == 'robinsons.xml') {
                    $xmlFile        = file_get_contents($url);
                    $xml            = simplexml_load_string($xmlFile, "SimpleXMLElement", LIBXML_NOCDATA);
                    $json           = json_encode($xml);
                    $array          = json_decode($json,TRUE);
                    $sheet_index    = null;
                    $items          = $array['content']['consumer']['stores'];
                    $line_numbers = [];

                    foreach (array_values($items) as $data) {
                        foreach (array_values($data) as $linenum) {
                            foreach ($linenum as $line) {
                                $line_numbers[] = $line;
                            }
                        }
                    }

                    sort($line_numbers);

                    $response['status']  = true;
                    $response['message'] = '';

                    $response['data']   = [
                        'company'     => $array['companyname'],
                        'output'      => $array['output'],
                        'sheet_index' => $sheet_index,
                        'file_type'   => $array['filetype'],
                        'templates'   => $line_numbers
                    ];
                } else {
                    $xml       = simplexml_load_file($url);
                    $xml_arr   = (array) $xml;

                    $company     = $xml_arr['companyname'];
                    $output      = $xml_arr['output'];
                    $sheet_index = $xml_arr['sheetindex'];
                    $content     = $xml_arr['content'];

                    $response['status']  = true;
                    $response['message'] = '';

                    // TODO: need to restructure this to be able to fit other format!
                    $response['data']    = [
                        'company'     => $company,
                        'output'      => $output,
                        'sheet_index' => $sheet_index,
                        'templates'   => $content
                    ];
                }
            }

        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return $response;
    }
}