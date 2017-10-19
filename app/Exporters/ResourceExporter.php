<?php

namespace App\Exporters;

use App\Score;
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
	        case 'players':
		        return static::generatePlayersExport();
		        break;
	        case 'player':
		        return static::generatePlayerExport();
		        break;
            case 'users':
                return static::generateUsersExport();
                break;
        }

        return null;
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

	/**
	 * Generate players export
	 * @return mixed
	 */
	public function generatePlayersExport()
	{
		return Excel::create($this->exportFileName, function($excel) {
			$resources = $this->resources;
			$exportArr = [];
			$latestGw = Score::findLatestGameWeek();

			if ( count($resources) ) {
				foreach ($resources as $resource) {
					$exportArr[] = [
						'Name' => $resource->name,
						'FPL ID' => $resource->fpl_id,
						'Team' => $resource->team_name,
						"Game-week $latestGw Points" => $resource->latest_points,
						'Total Points to Date' => $resource->total_points,
						'Created' => $resource->created_at->toDateTimeString(),
						'Last Updated' => $resource->updated_at->toDateTimeString(),
					];

				}
			}

			$excel->sheet('Players', function($sheet) use ($exportArr) {
				$sheet->fromArray($exportArr);
			});

		})->download('xls');
	}

	/**
	 * Generate single player export
	 * @return mixed
	 */
	public function generatePlayerExport()
	{
		return Excel::create($this->exportFileName, function($excel) {
			$resource = $this->resources;
			$playerDetails = [];
			$scoresArr = [];

			if ( $resource ) {
				$playerDetails[] = [
					'Name' => $resource->name,
					'FPL ID' => $resource->fpl_id,
					'Team' => $resource->team_name,
					'First Fetched' => $resource->created_at->toDateTimeString(),
					'Last Fetched' => $resource->updated_at->toDateTimeString(),
				];

				$excel->sheet('Details', function($sheet) use ($playerDetails) {
					$sheet->fromArray($playerDetails);
				});

				foreach ($resource->scores as $score) {
					$scoresArr[] = [
						'Game-week' => "Game-week {$score->game_week}",
						'Gross Points' => $score->raw_points,
						'Points Penalty' => $score->points_penalty,
						'Net Points' => $score->net_points,
						'Total Points' => $score->total_points,
						'First Fetched' => $score->created_at->toDateTimeString(),
						'Last Fetched' => $score->updated_at->toDateTimeString(),
					];

				}
			}

			$excel->sheet('Scores', function($sheet) use ($scoresArr) {
				$sheet->fromArray($scoresArr);
			});

		})->download('xls');
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
}