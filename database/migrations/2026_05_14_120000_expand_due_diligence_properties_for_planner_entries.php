<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('acquisition_due_diligence_properties', function (Blueprint $table) {
			$table->decimal('offer_price', 15, 2)->nullable()->after('property_label');

			$table->string('findings_report_path')->nullable()->after('offer_price');
			$table->text('findings_report_comment')->nullable()->after('findings_report_path');

			$table->text('filter_viable_option')->nullable()->after('findings_report_comment');
			$table->text('shortlisted_properties')->nullable()->after('filter_viable_option');

			$table->string('parcel_plot_file_path')->nullable()->after('parcel_plot');
			$table->text('parcel_plot_comment')->nullable()->after('parcel_plot_file_path');

			$table->string('coordinates_file_path')->nullable()->after('coordinates');
			$table->text('coordinates_comment')->nullable()->after('coordinates_file_path');

			$table->string('extent_file_path')->nullable()->after('extent');
			$table->text('extent_comment')->nullable()->after('extent_file_path');

			$table->string('township')->nullable()->after('district');
		});

		// Backfill: migrate legacy single-value planner/surveyor fields into the first property row
		// for due diligence records that don't yet have any properties.
		if (Schema::hasTable('acquisition_due_diligences')) {
			$dueDiligences = DB::table('acquisition_due_diligences')
				->select([
					'id',
					'potential_properties',
					'offer_price',
					'findings_report_path',
					'findings_report_comment',
					'filter_viable_option',
					'shortlisted_properties',
					'property_no',
					'property_no_file_path',
					'property_no_comment',
					'coordinates',
					'coordinates_file_path',
					'coordinates_comment',
					'land_extent',
					'land_extent_file_path',
					'land_extent_comment',
					'province',
					'district',
					'township',
					'chiefdom',
				])
				->get();

			foreach ($dueDiligences as $dd) {
				$alreadyHasProperties = DB::table('acquisition_due_diligence_properties')
					->where('acquisition_due_diligence_id', $dd->id)
					->exists();

				if ($alreadyHasProperties) {
					continue;
				}

				$hasLegacyData = (bool) (
					$dd->potential_properties ||
					$dd->offer_price ||
					$dd->findings_report_path ||
					$dd->findings_report_comment ||
					$dd->filter_viable_option ||
					$dd->shortlisted_properties ||
					$dd->property_no ||
					$dd->property_no_file_path ||
					$dd->property_no_comment ||
					$dd->coordinates ||
					$dd->coordinates_file_path ||
					$dd->coordinates_comment ||
					$dd->land_extent ||
					$dd->land_extent_file_path ||
					$dd->land_extent_comment ||
					$dd->province ||
					$dd->district ||
					$dd->township ||
					$dd->chiefdom
				);

				if (! $hasLegacyData) {
					continue;
				}

				$extentNumeric = null;
				if (is_numeric($dd->land_extent)) {
					$extentNumeric = (float) $dd->land_extent;
				}

				DB::table('acquisition_due_diligence_properties')->insert([
					'acquisition_due_diligence_id' => $dd->id,
					'property_label' => $dd->potential_properties
						? Str::limit((string) $dd->potential_properties, 255, '')
						: 'Property 1',

					'offer_price' => $dd->offer_price,
					'findings_report_path' => $dd->findings_report_path,
					'findings_report_comment' => $dd->findings_report_comment,

					'filter_viable_option' => $dd->filter_viable_option,
					'shortlisted_properties' => $dd->shortlisted_properties,

					'parcel_plot' => $dd->property_no,
					'parcel_plot_file_path' => $dd->property_no_file_path,
					'parcel_plot_comment' => $dd->property_no_comment,

					'coordinates' => $dd->coordinates,
					'coordinates_file_path' => $dd->coordinates_file_path,
					'coordinates_comment' => $dd->coordinates_comment,

					'extent' => $extentNumeric,
					'extent_file_path' => $dd->land_extent_file_path,
					'extent_comment' => $dd->land_extent_comment,

					'province' => $dd->province,
					'district' => $dd->district,
					'township' => $dd->township,
					'chiefdom' => $dd->chiefdom,

					'created_at' => now(),
					'updated_at' => now(),
				]);
			}
		}
	}

	public function down(): void
	{
		Schema::table('acquisition_due_diligence_properties', function (Blueprint $table) {
			$table->dropColumn([
				'offer_price',
				'findings_report_path',
				'findings_report_comment',
				'filter_viable_option',
				'shortlisted_properties',
				'parcel_plot_file_path',
				'parcel_plot_comment',
				'coordinates_file_path',
				'coordinates_comment',
				'extent_file_path',
				'extent_comment',
				'township',
			]);
		});
	}
};
