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
	        case 'leagues':
		        return static::generateLeaguesExport();
		        break;
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
     * Generate leagues export
     * @return mixed
     */
    public function generateLeaguesExport()
    {
        return Excel::create($this->exportFileName, function($excel) {
            $resources = $this->resources;
            $exportArr = [];

            if ( count($resources) ) {
                foreach ($resources as $resource) {
                    $exportArr[] = [
                        'Name' => $resource->name,
                        'FPL ID' => $resource->fpl_id,
                        'Total Players' => $resource->players_count,
                        'Admin' => $resource->admin_name,
                        'Admin Team' => $resource->admin_team_name,
                        'Created' => $resource->created_at->toDateTimeString(),
                        'Last Updated' => $resource->updated_at->toDateTimeString(),
                    ];

                }
            }

            $excel->sheet('Leagues', function($sheet) use ($exportArr) {
                $sheet->fromArray($exportArr);
            });

        })->download('xls');
    }

}