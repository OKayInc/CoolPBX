<?php

namespace App\Services;

use App\Facades\Setting;
use App\Models\Fax;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use setasign\Fpdi\Tcpdf\Fpdi;
use TCPDF_FONTS;

class FaxService
{
	protected $fax_instance_uuid;
	protected $dir_fax_inbox;
	protected $dir_fax_sent;
	protected $dir_fax_temp;
	protected $fax_cover_font;
	protected $page_width;
	protected $page_height;
	protected $gs_r;
	protected $gs_g;
	protected $fax_page_count;
	protected $tif_files;

	public function sendFax(Fax $fax, $faxData)
	{
		if(auth()->user()->hasPermission('fax_inbox_view'))
		{
			//set the fax directory
			$fax_dir = Setting::getSetting('switch', 'storage', 'dir') . '/fax/' . Session::get('domain_name');

			//set fax cover font to generate pdf
			$this->fax_cover_font = Setting::getSetting('fax', 'cover_font', 'text') ?? null;

			if(!empty($fax->fax_extension) && is_numeric($fax->fax_extension))
			{
				//set the fax directories. example /usr/local/freeswitch/storage/fax/329/inbox
				$this->dir_fax_inbox = $fax_dir .'/'. $fax->fax_extension . '/inbox';
				$this->dir_fax_sent = $fax_dir .'/'. $fax->fax_extension . '/sent';
				$this->dir_fax_temp = $fax_dir .'/'. $fax->fax_extension . '/temp';

				//make sure the directories exist
				$switchStorageDir = Setting::getSetting('switch', 'storage', 'dir');

				if(!is_dir($switchStorageDir))
				{
					mkdir($switchStorageDir, 0770, true);
				}

				if(!is_dir($switchStorageDir . '/fax'))
				{
					mkdir($switchStorageDir . '/fax', 0770, true);
				}

				if(!is_dir($switchStorageDir . '/fax/' . Session::get('domain_name')))
				{
					mkdir($switchStorageDir . '/fax/' . Session::get('domain_name'), 0770, true);
				}

				if(!is_dir($fax_dir . '/' . $fax->fax_extension))
				{
					mkdir($fax_dir . '/' . $fax->fax_extension, 0770, true);
				}

				if(!is_dir($this->dir_fax_inbox))
				{
					mkdir($this->dir_fax_inbox, 0770, true);
				}

				if(!is_dir($this->dir_fax_sent))
				{
					mkdir($this->dir_fax_sent, 0770, true);
				}

				if(!is_dir($this->dir_fax_temp))
				{
					mkdir($this->dir_fax_temp, 0770, true);
				}

				$domain_enabled = !empty(Setting::getSetting('domains', Session::get('domain_uuid'), 'domain_enabled'));

				//clear file status cache
				clearstatcache();

				//send the fax
				if($domain_enabled)
				{
					//cleanup numbers
					if(!empty($faxData["fax_numbers"]))
					{
						foreach($faxData["fax_numbers"] as $index => $fax_number)
						{
							fax_split_dtmf($fax_number, $fax_dtmf);

							$fax_number = preg_replace("~[^0-9]~", "", $fax_number);
							$fax_dtmf   = preg_replace("~[^0-9Pp*#]~", "", $fax_dtmf);

							if($fax_number != '')
							{
								if($fax_dtmf != '')
								{
									$fax_number .= " (" . $fax_dtmf . ")";
								}

								$faxData["fax_numbers"][$index] = $fax_number;
							}
							else
							{
								unset($faxData["fax_numbers"][$index]);
							}
						}

						sort($faxData["fax_numbers"]);
					}

					//determine page size
					switch($faxData["fax_page_size"])
					{
						case 'a4' :
							$this->page_width = 8.3; //in
							$this->page_height = 11.7; //in
							break;
						case 'legal' :
							$this->page_width = 8.5; //in
							$this->page_height = 14; //in
							break;
						case 'letter' :
							$this->page_width = 8.5; //in
							$this->page_height = 11; //in
							break;
						default	:
							$this->page_width = 8.5; //in
							$this->page_height = 11; //in
					}

					//set resolution
					switch($faxData["fax_resolution"])
					{
						case 'fine':
							$this->gs_r = '204x196';
							$this->gs_g = ((int) ($this->page_width * 204)).'x'.((int) ($this->page_height * 196));
							break;
						case 'superfine':
							$this->gs_r = '204x392';
							$this->gs_g = ((int) ($this->page_width * 204)).'x'.((int) ($this->page_height * 392));
							break;
						case 'normal':
						default:
							$this->gs_r = '204x98';
							$this->gs_g = ((int) ($this->page_width * 204)).'x'.((int) ($this->page_height * 98));
							break;
					}

					$this->processFiles($fax, $faxData);
					$this->generatePDF($fax, $faxData);
					$this->combineFiles($fax, $faxData);

					return true;
				}
			}
		}

		return false;
	}

	private function processFiles(Fax $fax, $faxData)
	{
		//process uploaded or emailed files (if any)
		$this->fax_page_count = 0;

		$files = $faxData['fax_files'];

		$this->tif_files = [];

		foreach($files as $file)
		{
			if($file->isValid() && $file->getSize() > 0)
			{
				//get the file extension
				$fax_file_extension = strtolower($file->getClientOriginalExtension());

				if($fax_file_extension == "tiff")
				{
					$fax_file_extension = "tif";
				}

				//block unauthorized files
				$disallowed_file_extensions = explode(',','sh,ssh,so,dll,exe,bat,vbs,zip,rar,z,tar,tbz,tgz,gz');

				if(in_array($fax_file_extension, $disallowed_file_extensions) || $fax_file_extension == '')
				{
					continue;
				}

				//use a safe file name
				$fax_name = md5($file->getClientOriginalName());

				//check if directory exists
				if(!is_dir($this->dir_fax_temp))
				{
					mkdir($this->dir_fax_temp, 0770, true);
				}

				//move uploaded file
				$file->storeAs(str_replace(storage_path('app/') , '', $this->dir_fax_temp), $fax_name . '.' . $fax_file_extension);

				//convert uploaded file to pdf, if necessary
				if($fax_file_extension != "pdf" && $fax_file_extension != "tif")
				{
					chdir($this->dir_fax_temp);

					$command = is_windows() ? '' : 'export HOME=/tmp && ';
					$command .= 'libreoffice --headless --convert-to pdf --outdir ' . $this->dir_fax_temp . ' ' . $this->dir_fax_temp . '/' . escapeshellarg($fax_name) . '.' . escapeshellarg($fax_file_extension);

					exec($command);

					@unlink($this->dir_fax_temp . '/' . $fax_name . '.' . $fax_file_extension);
				}

				//convert uploaded pdf to tif
				if(file_exists($this->dir_fax_temp . '/' . $fax_name . '.pdf'))
				{
					chdir($this->dir_fax_temp);

					//convert pdf to tif
					$cmd = exec('which gs')." -q -r" . $this->gs_r . " -g" . $this->gs_g . " -dBATCH -dPDFFitPage -dNOSAFER -dNOPAUSE -dBATCH -sOutputFile=" . escapeshellarg($fax_name) . ".tif -sDEVICE=tiffg4 -Ilib stocht.ps -c \"{ .75 gt { 1 } { 0 } ifelse} settransfer\" -- " . escapeshellarg($fax_name) . ".pdf -c quit";

					exec($cmd);

					@unlink($this->dir_fax_temp . '/' . $fax_name . '.pdf');
				}

				//get the page count
				$cmd = exec('which tiffinfo')." ".correct_path($this->dir_fax_temp . '/' . $fax_name).".tif | grep \"Page Number\" | grep -c \"P\"";

				$tif_page_count = exec($cmd);

				if($tif_page_count != '')
				{
					$this->fax_page_count += $tif_page_count;
				}

				//add file to array
				$this->tif_files[] = $this->dir_fax_temp . '/' . $fax_name . '.tif';
			}
		}
	}

	private function generatePDF(Fax $fax, $faxData)
	{
		// unique id for this fax
		$this->fax_instance_uuid = Str::uuid();

		//generate cover page, merge with pdf
		if($faxData["fax_subject"] != '' || $faxData["fax_message"] != '')
		{
			// initialize pdf
			$pdf = new FPDI('P', 'in');
			$pdf->SetAutoPageBreak(false);
			$pdf->setPrintHeader(false);
			$pdf->setPrintFooter(false);
			$pdf->SetMargins(0, 0, 0, true);

			if(!empty($this->fax_cover_font))
			{
				if(substr($this->fax_cover_font, -4) == '.ttf')
				{
					$pdf_font = TCPDF_FONTS::addTTFfont($this->fax_cover_font);
				}
				else
				{
					$pdf_font = $this->fax_cover_font;
				}
			}

			if(empty($pdf_font) || !$pdf_font)
			{
				$pdf_font = 'times';
			}

			//add blank page
			$pdf->AddPage('P', array($this->page_width, $this->page_height));

			// content offset, if necessary
			$x = 0;
			$y = 0;

			//logo
			$display_logo = false;

			$faxCoverLogo = Setting::getSetting("fax", "cover_logo");
			$faxCoverLogoText = Setting::getSetting("fax", "cover_logo", "text");

			if(empty($faxCoverLogo) || !is_array($faxCoverLogo))
			{
				$logo = public_path("assets/images/logo.jpg");

				$display_logo = true;
			}
			else if(is_null($faxCoverLogoText))
			{
				$logo = ''; //explicitly empty
			}
			else if($faxCoverLogoText != '')
			{
				if(Str::startsWith($faxCoverLogoText, ['http://', 'https://']))
				{
					$logo = $faxCoverLogoText;

				}
				elseif(Str::startsWith($faxCoverLogoText, '/'))
				{
					if(!Str::startsWith($faxCoverLogoText, public_path()))
					{
						$logo = public_path(ltrim($faxCoverLogoText, '/'));
					}
					else
					{
						$logo = $faxCoverLogoText;
					}
				}
				else
				{
					$logo = $faxCoverLogoText;
				}
			}

			if(isset($logo) && $logo)
			{
				$logo_dirname = strtolower(pathinfo($logo, PATHINFO_DIRNAME));
				$logo_filename = strtolower(pathinfo($logo, PATHINFO_BASENAME));
				$logo_fileext = pathinfo($logo_filename, PATHINFO_EXTENSION);

				if(in_array($logo_fileext, ['gif','jpg','jpeg','png','bmp']))
				{
					if(file_exists($logo_dirname . '/' . $logo_filename))
					{
						$logo = $logo_dirname . '/' . $logo_filename;

						$display_logo = true;
					}
					else
					{
						$raw = file_get_contents($logo);

						if(file_put_contents($this->dir_fax_temp . '/' . $logo_filename, $raw))
						{
							$logo = $this->dir_fax_temp . '/' . $logo_filename;

							$display_logo = true;
						}
						else
						{
							unset($logo);
						}
					}
				}
				else
				{
					unset($logo);
				}
			}

			if($display_logo)
			{
				$pdf->Image($logo, 0.5, 0.4, 2.5, 0.9, null, null, 'N', true, 300, null, false, false, 0, true);
			}
			else
			{
				//set position for header text, if enabled
				$pdf->SetXY($x + 0.5, $y + 0.4);
			}

			//header
			if($faxData["fax_header"] != '')
			{
				$pdf->SetLeftMargin(0.5);
				$pdf->SetFont($pdf_font, "", 10);
				$pdf->Write(0.3, $faxData["fax_header"]);
			}

			//fax, cover sheet
			$pdf->SetTextColor(0,0,0);
			$pdf->SetFont($pdf_font, "B", 55);
			$pdf->SetXY($x + 4.55, $y + 0.25);
			$pdf->Cell($x + 3.50, $y + 0.4, __('Fax'), 0, 0, 'R', false, null, 0, false, 'T', 'T');
			$pdf->SetFont($pdf_font, "", 12);
			$pdf->SetFontSpacing(0.0425);
			$pdf->SetXY($x + 4.55, $y + 1.0);
			$pdf->Cell($x + 3.50, $y + 0.4, __('COVER SHEET'), 0, 0, 'R', false, null, 0, false, 'T', 'T');
			$pdf->SetFontSpacing(0);

			//field labels
			$pdf->SetFont($pdf_font, "B", 12);

			if($faxData["fax_recipient"] != '' || sizeof($faxData["fax_numbers"]) > 0)
			{
				$pdf->Text($x + 0.5, $y + 2.0, strtoupper(__('To')) . ":");
			}

			if($faxData["fax_sender"] != '' || $fax->fax_caller_id_number != '')
			{
				$pdf->Text($x + 0.5, $y + 2.3, strtoupper(__('From')) . ":");
			}

			if($this->fax_page_count > 0)
			{
				$pdf->Text($x + 0.5, $y + 2.6, strtoupper(__('Attached')).":");
			}

			if($faxData["fax_subject"] != '')
			{
				$pdf->Text($x + 0.5, $y + 2.9, strtoupper(__('Subject')).":");
			}

			//field values
			$pdf->SetFont($pdf_font, "", 12);
			$pdf->SetXY($x + 2.0, $y + 1.95);

			if($faxData["fax_recipient"] != '')
			{
				$pdf->Write(0.3, $faxData["fax_recipient"]);
			}

			if(sizeof($faxData["fax_numbers"]) > 0)
			{
				$fax_number_string = ($faxData["fax_recipient"] != '') ? ' (' : null;
				$fax_number_string .= format_phone($faxData["fax_numbers"][0]);

				if(sizeof($faxData["fax_numbers"]) > 1)
				{
					for($n = 1; $n <= sizeof($faxData["fax_numbers"]); $n++)
					{
						if($n == 4)
						{
							break;
						}

						$fax_number_string .= ', '.format_phone($faxData["fax_numbers"][$n - 1]);
					}
				}

				$fax_number_string .= (sizeof($faxData["fax_numbers"]) > 4) ? ', +' . (sizeof($faxData["fax_numbers"]) - 4) : null;
				$fax_number_string .= ($faxData["fax_recipient"] != '') ? ')' : null;

				$pdf->Write(0.3, $fax_number_string);
			}

			$pdf->SetXY($x + 2.0, $y + 2.25);

			if($faxData["fax_sender"] != '')
			{
				$pdf->Write(0.3, $faxData["fax_sender"]);

				if($fax->fax_caller_id_number != '')
				{
					$pdf->Write(0.3, '  (' . format_phone($fax->fax_caller_id_number) . ')');
				}
			}
			else
			{
				if($fax->fax_caller_id_number != '')
				{
					$pdf->Write(0.3, format_phone($fax->fax_caller_id_number));
				}
			}

			if($this->fax_page_count > 0)
			{
				$pdf->Text($x + 2.0, $y + 2.6, $this->fax_page_count . ' ' . __('Page') . ($this->fax_page_count > 1) ? 's' : '');
			}

			if($faxData["fax_subject"] != '')
			{
				$pdf->Text($x + 2.0, $y + 2.9, $faxData["fax_subject"]);
			}

			//message
			if($faxData["fax_message"] != '')
			{
				$pdf->SetAutoPageBreak(true, 0.6);
				$pdf->SetTopMargin(0.6);
				$pdf->SetFont($pdf_font, "", 12);
				$pdf->SetXY($x + 0.75, $y + 3.65);
				$pdf->MultiCell(7, 5.40, $faxData["fax_message"], 0, 'L', false);
			}

			$pages = $pdf->getNumPages();

			if($pages > 1)
			{
				//save ynew for last page
				$yn = $pdf->GetY();

				//first page
				$pdf->setPage(1, 0);
				$pdf->Rect($x + 0.5, $y + 3.4, 7.5, $this->page_height - 3.9, 'D');

				//2nd to n-th page
				for($n = 2; $n < $pages; $n++)
				{
					$pdf->setPage($n, 0);
					$pdf->Rect($x + 0.5, $y + 0.5, 7.5, $this->page_height - 1, 'D');
				}

				//last page
				$pdf->setPage($pages, 0);
				$pdf->Rect($x + 0.5, 0.5, 7.5, $yn, 'D');

				$y = $yn;

				unset($yn);
			}
			else
			{
				$pdf->Rect($x + 0.5, $y + 3.4, 7.5, 6.25, 'D');

				$y = $pdf->GetY();
			}

			//footer
			if($faxData["fax_footer"] != '')
			{
				$pdf->SetAutoPageBreak(true, 0.6);
				$pdf->SetTopMargin(0.6);
				$pdf->SetFont("helvetica", "", 8);
				$pdf->SetXY($x + 0.5, $y + 0.6);
				$pdf->MultiCell(7.5, 0.75, $faxData["fax_footer"], 0, 'C', false);
			}

			$pdf->SetAutoPageBreak(false);
			$pdf->SetTopMargin(0);

			//save cover pdf
			$pdf->Output($this->dir_fax_temp . '/' . $this->fax_instance_uuid . '_cover.pdf', "F");	// Display [I]nline, Save to [F]ile, [D]ownload

			//convert pdf to tif, add to array of pages, delete pdf
			if(file_exists($this->dir_fax_temp . '/' . $this->fax_instance_uuid.'_cover.pdf'))
			{
				chdir($this->dir_fax_temp);

				$cmd = gs_cmd("-q -sDEVICE=tiffg32d -r" . $this->gs_r . " -g" . $this->gs_g . " -dBATCH -dPDFFitPage -dNOSAFER -dNOPAUSE -sOutputFile=" . correct_path($this->fax_instance_uuid) . "_cover.tif -- " . correct_path($this->fax_instance_uuid) . "_cover.pdf -c quit");

				exec($cmd);

				if(!empty($this->tif_files) && is_array($this->tif_files) && sizeof($this->tif_files) > 0)
				{
					array_unshift($this->tif_files, $this->dir_fax_temp . '/' . $this->fax_instance_uuid . '_cover.tif');
				}
				else
				{
					$this->tif_files[] = $this->dir_fax_temp . '/' . $this->fax_instance_uuid . '_cover.tif';
				}

				@unlink($this->dir_fax_temp . '/' . $this->fax_instance_uuid . '_cover.pdf');
			}
		}
	}

	private function combineFiles(Fax $fax, $faxData)
	{
		$tiffcp = exec('which tiffcp');
		$tiff2pdf = exec('which tiff2pdf');

		if(empty($tiffcp) || empty($tiff2pdf))
		{
			Log::error('TIFF tools not found on system.');

			return;
		}

		//combine tif files into single multi-page tif
		if(!empty($this->tif_files) && is_array($this->tif_files) && sizeof($this->tif_files) > 0)
		{
			$cmd = exec('which tiffcp') . " -c none ";

			foreach($this->tif_files as $tif_file)
			{
				$cmd .= correct_path($tif_file) . ' ';
			}

			$cmd .= correct_path($this->dir_fax_sent . '/' . $this->fax_instance_uuid . '.tif');

			exec($cmd);

			//generate pdf from tif
			$cmd = exec('which tiff2pdf').' -u i -p ' . $faxData["fax_page_size"].
				' -w ' . $this->page_width.
				' -l ' . $this->page_height.
				' -f -o '.
				correct_path($this->dir_fax_sent . '/' . $this->fax_instance_uuid.'.pdf') . ' '.
				correct_path($this->dir_fax_sent . '/' . $this->fax_instance_uuid.'.tif');

			exec($cmd);

			// remove the extra files
			foreach($this->tif_files as $tif_file)
			{
				@unlink($tif_file);
			}
		}
	}
}
