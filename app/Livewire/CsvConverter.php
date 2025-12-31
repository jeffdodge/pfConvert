<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CsvConverter extends Component
{
    use WithFileUploads;

    public $file;

    protected $rules = [
        'file' => 'required|file|mimes:csv,txt|max:20480',
    ];

    /**
     * INPUT → OUTPUT COLUMN MAPPING
     * input_header => output_header
     */
    private array $columnMap = [

        // IDs
        'Employment Type'         => 'Employee Type',

        // Names
        'Legal first name'        => 'First Name',
        'Legal middle name'       => 'Middle Name',
        'Legal last name'         => 'Last Name',
        'Preferred first name'    => 'Preferred First Name',
        'Preferred last name'     => 'Preferred Last Name',

        // Company
        'Legal Entity Name'       => 'IC Company Name',

        // Sensitive
        'SSN'                     => 'SSN',
        'Date of birth'           => 'Birth Date',

        // Demographics
        'Legal gender'                           => 'Sex',
        'Nationality (as selected during hire)'  => 'Ethnic Origin',

        // Address
        'Home address - Street address'     => 'Address 1',
        'Home address - City'               => 'City',
        'Home address - State'              => 'State / Province',
        'Home address - Zip'                => 'Zip / Postal Code',
        'Home address - Country'            => 'Country',

        // Phones
        'Phone number'            => 'Cell Phone',

        // Employment
        'Full-time'               => 'Full/Part Time',
    ];

    public function convert()
    {
        $this->validate();

        $inputPath = $this->file->getRealPath();

        // Ensure directory exists
        Storage::disk('local')->makeDirectory('converted');

        $filename = 'paychex-converted_' . now()->format('Ymd_His') . '_' . Str::random(6) . '.csv';
        $relativePath = 'converted/' . $filename;
        $absolutePath = Storage::disk('local')->path($relativePath);

        $this->convertCsv($inputPath, $absolutePath);

        return response()->download($absolutePath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * MAIN CONVERSION LOGIC
     */
    private function convertCsv(string $inPath, string $outPath): void
    {
        $in = fopen($inPath, 'r');
        $out = fopen($outPath, 'w');

        // INPUT HEADERS
        $inputHeaders = array_map('trim', fgetcsv($in));
        $inIndex = [];
        foreach ($inputHeaders as $i => $h) {
            $inIndex[$h] = $i;
        }

        // OUTPUT HEADERS
        $outHeaders = $this->outputHeaders();
        $outIndex = [];
        foreach ($outHeaders as $i => $h) {
            $outIndex[$h] = $i;
        }

        fputcsv($out, $outHeaders);

        // STREAM ROWS
        while (($row = fgetcsv($in)) !== false) {
            $outRow = array_fill(0, count($outHeaders), '');

            foreach ($this->columnMap as $inputCol => $outputCol) {
                if (!isset($inIndex[$inputCol], $outIndex[$outputCol])) {
                    continue;
                }

                $value = trim((string)($row[$inIndex[$inputCol]] ?? ''));

                // Normalizers
                if ($outputCol === 'SSN') {
                    $value = $this->normalizeSsn($value);
                } elseif ($outputCol === 'Birth Date') {
                    $value = $this->normalizeDate($value);
                } elseif ($outputCol === 'Cell Phone') {
                    $value = $this->normalizePhone($value);
                } elseif ($outputCol === 'Full/Part Time') {
                    $value = $this->toFullPart($value);
                }

                $outRow[$outIndex[$outputCol]] = $value;
            }

            fputcsv($out, $outRow);
        }

        fclose($in);
        fclose($out);
    }

    /**
     * OUTPUT HEADERS
     */
    private function outputHeaders(): array
    {
        return array_map('trim', explode("\t", <<<HDR
Company ID	Employee ID	Employee Type	First Name	Middle Name	Last Name	Preferred First Name	Preferred Last Name	Prefix	Suffix	IC Company Name	SSN	Federal ID Number	Birth Date	Clock ID	Sex	Preferred Pronouns	Ethnic Origin	Address 1	Address 2	PO Box Number	City	State / Province	Zip / Postal Code	Country	Telephone	Cell Phone	Work Phone	Work Phone Ext	Full/Part Time	Eligible for Retirement Plan	PEO Class Code	Organization Unit	Business Location Name	Position	Work From Home	EEO Job Category	Work State	Officer Type	Class Code	Class Code Suffix	Waive Code	Employee Status	Reason	Reason Description	Status Date	Keep DD Active	Deactivate Date	Supervisor	Pay Rate 1	Pay Rate Amount 1	Pay Rate 1 Org Unit	Pay Rate # 	Pay Rate Amount #	Pay Rate # Org Unit	Pay Rate Default	Pay Frequency	Standard Pay Hours	Standard OT Hours	Standard Hours #	Standard OT Hours #	Overtime Exempt	Federal Tax Residency	Federal Taxability Status	Federal Filing Status	Federal Allowances	Multiple Jobs	Dependents Amount	Deductions Amount	Other Income	Federal Additional Tax Amount	Federal Additional Tax Percentage	Federal Override Tax Amount	Federal Override Tax Percentage	EE Sequence	State Income Tax	State Tax Residency	State Percent Worked	State Taxability Status	State Unemployment	Worksite Code	State Disability	State Filing Status	State Allowance Name 1	State Allowance Number 1	State Allowance Amount 1	State Allowance Name 2	State Allowance Number 2	State Allowance Amount 2	State Allowance Name 3	State Allowance Number 3	State Allowance Amount 3	State Additional Tax Amount	State Additional Tax Percentage	State Override Tax Amount	State Override Tax Percentage	Reduced Withholding Amount	State Withholding %	Dependent Health Insurance Benefits Indicator	Date Dependent Health Ins Benefits are Available	Employee Health Insurance Benefits Indicator	Date Employee Health Ins Benefits are Available	County	Family Owned Business Owner Indicator	Seasonal Indicator	SOC	SUI County	State Jurisdiction 1	Local Regulation Name 1	Local Tax Residency 1	PA Live/Work Status 1	Local Taxability Status 1	Local Filing Status 1	Local Allowance Name 1	Local Allowance Number 1	% of Earnings Taxed 1	Ohio Local Residence Tax Rate 1	Local Additional Tax Amount 1	Local Additional Tax Percent 1	Local Override Tax Amount 1	Local Override Tax Percent 1	Employee Waiver Indicator 1	Local County 1	State Jurisdiction 2	Local Regulation Name 2	Local Tax Residency 2	PA Live/Work Status 2	Local Taxability Status 2	Local Filing Status 2	Local Allowance Name 2	Local Allowance Number 2	% of Earnings Taxed 2	Ohio Local Residence Tax Rate 2	Local Additional Tax Amount 2	Local Additional Tax Percent 2	Local Override Tax Amount 2	Local Override Tax Percent 2	Employee Waiver Indicator 2	Local County 2	State Jurisdiction 3	Local Regulation Name 3	Local Tax Residency 3	PA Live/Work Status 3	Local Taxability Status 3	Local Filing Status 3	Local Allowance Name 3	Local Allowance Number 3	% of Earnings Taxed 3	Ohio Local Residence Tax Rate 3	Local Additional Tax Amount 3	Local Additional Tax Percent 3	Local Override Tax Amount 3	Local Override Tax Percent 3	Employee Waiver Indicator 3	Local County 3	Job Number	Labor Assignment	Home Email	Work Email	Hired Date	ESR Standard Hours	ESR Standard Hours Same as Standard Hours	Insurance Standard Hours	Insurance Standard Hours Same as Standard Hours
HDR));
    }

    /**
     * HELPERS
     */
    private function getValue(array $row, array $index, string $key): string
    {
        return $index[$key] ?? null
            ? trim((string)($row[$index[$key]] ?? ''))
            : '';
    }

    private function setOut(array &$row, array $index, string $col, string $value): void
    {
        if (isset($index[$col])) {
            $row[$index[$col]] = $value;
        }
    }

    private function normalizeSsn(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw);
        return strlen($digits) === 9
            ? substr($digits, 0, 3) . '-' . substr($digits, 3, 2) . '-' . substr($digits, 5)
            : '';
    }

    private function normalizeDate(string $raw): string
    {
        $ts = strtotime($raw);
        return $ts ? date('Y-m-d', $ts) : '';
    }

    private function normalizePhone(string $raw): string
    {
        return preg_replace('/\D+/', '', $raw);
    }

    private function toFullPart(string $raw): string
    {
        $v = strtolower(trim($raw));
        return in_array($v, ['1','yes','true','full-time','full time']) ? 'Full Time'
            : (in_array($v, ['0','no','false','part-time','part time']) ? 'Part Time' : '');
    }

    public function render()
    {
        return view('livewire.csv-converter');
    }
}
