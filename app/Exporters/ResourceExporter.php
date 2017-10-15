<?php

namespace App\Exporters;

use Maatwebsite\Excel\Facades\Excel;

class ResourceExporter
{

    public $resources;
    public $exportFileName;

    /**
     * ResourceExporter constructor.
     * @param $resources
     * @param $fileName
     */
    public function __construct($resources, $fileName)
    {
        $this->resources = $resources;
        $this->exportFileName = $fileName;
    }

	/**
	 * Generate excel export
	 * @param $type
	 *
	 * @return mixed|null
	 */
    public function generateExcelExport($type)
    {
        switch ($type) {
            case 'users':
                return static::generateUsersExport();
                break;
        }

        return null;
    }

    /**
     * Generate users export
     * @return mixed
     */
    public function generateUsersExport()
    {
        return Excel::create($this->exportFileName, function($excel) {
            $resources = $this->resources;
            $exportArr = [];

            if ( count($resources) ) {
                foreach ($resources as $resource) {
                    $exportArr[] = [
                        'First Name' => $resource->first_name,
                        'Last Name' => $resource->last_name,
                        'Email' => $resource->email,
                        'Username' => $resource->username,
                        'Active' => $resource->active ? '✔' : '✗',
                        'Role' => $resource->is_super_admin ? 'Super Admin' : 'User',
                        'User Since' => $resource->created_at->toDateTimeString(),
                    ];

                }
            }

            $excel->sheet('Users', function($sheet) use ($exportArr) {
                $sheet->fromArray($exportArr);
            });

        })->download('xls');
    }

    /**
     * Generate mailing lists export
     * @return mixed
     */
    public function generateMailingListsExport()
    {
        return Excel::create($this->exportFileName, function($excel) {
            $resources = $this->resources;
            $exportArr = [];

            if ( count($resources) ) {
                foreach ($resources as $resource) {
                    $exportArr[] = [
                        'Name' => $resource->name,
                        'Description' => $resource->description,
                        'Created' => $resource->created_at->toDateTimeString(),
                        'Last Updated' => $resource->updated_at->toDateTimeString(),
                    ];

                }
            }

            $excel->sheet('Mailing Lists', function($sheet) use ($exportArr) {
                $sheet->fromArray($exportArr);
            });

        })->download('xls');
    }

}