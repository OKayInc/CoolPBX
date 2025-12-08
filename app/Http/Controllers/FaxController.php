<?php
namespace App\Http\Controllers;

use App\Http\Requests\FaxRequest;
use App\Models\Contact;
use App\Models\Fax;
use App\Models\User;
use App\Repositories\FaxRepository;
use Illuminate\Support\Facades\Session;

class FaxController extends Controller
{
	protected $faxRepository;

	public function __construct(FaxRepository $faxRepository)
	{
		$this->faxRepository = $faxRepository;
	}
	public function index()
	{
		return view('pages.faxes.index');
	}

	public function create()
	{
		$fax_users = [];

		$available_users = User::all();

		return view("pages.faxes.form", compact("fax_users", "available_users"));
	}

	public function store(FaxRequest $request)
	{
		$fax = $this->faxRepository->create($request->validated());

		return redirect()->route("faxes.edit", $fax->fax_uuid);
	}

    public function show(Fax $fax)
    {
        //
    }

	public function edit(Fax $fax)
	{
		$fax_users = $fax->users;

		$available_users = User::whereNotIn('user_uuid', $fax_users->pluck('user_uuid'))->get();

		return view("pages.faxes.form", compact("fax", "fax_users", "available_users"));
	}

	public function update(FaxRequest $request, Fax $fax)
	{
		$this->faxRepository->update($fax, $request->validated());

        return redirect()->route("faxes.edit", $fax->fax_uuid);
	}

    public function destroy(Fax $fax)
    {
        $this->faxRepository->delete($fax);

        return redirect()->route('faxes.index');
    }

	public function send(Fax $fax)
	{
		$groups = auth()->user()->groups;

		foreach($groups as $group)
		{
			$userGroupUuids[] = $group['group_uuid'];
		}

		$userGroupUuids[] = auth()->user()->user_uuid;

		$domainUuid = Session::get('domain_uuid');

		$query = Contact::select([
				'v_contacts.contact_organization',
				'v_contacts.contact_name_given',
				'v_contacts.contact_name_family',
				'v_contacts.contact_nickname',
				'v_contact_phones.phone_number',
			])
			->join('v_contact_phones', 'v_contacts.contact_uuid', '=', 'v_contact_phones.contact_uuid')
			->where('v_contacts.domain_uuid', $domainUuid)
			->where('v_contact_phones.domain_uuid', $domainUuid)
			->where('v_contact_phones.phone_type_fax', 1)
			->whereNotNull('v_contact_phones.phone_number')
			->where('v_contact_phones.phone_number', '<>', '');

		if(Session::get('contact.permissions.boolean') === 'true' && !empty($userGroupUuids))
		{
			$query->where(function ($q) use ($userGroupUuids, $domainUuid) {
				$q->whereIn('v_contacts.contact_uuid', function ($sub) use ($userGroupUuids, $domainUuid) {
					$sub->select('contact_uuid')
						->from('v_contact_groups')
						->where('domain_uuid', $domainUuid)
						->whereIn('group_uuid', $userGroupUuids);
				})
				->orWhereNotIn('v_contacts.contact_uuid', function ($sub) use ($domainUuid) {
					$sub->select('contact_uuid')
						->from('v_contact_groups')
						->where('domain_uuid', $domainUuid);
				});
			});
		}

		$rows = $query->get();

		$contacts = [];

		foreach($rows as $row)
		{
			$contact_option_label = "";

			if($row['contact_organization'] != '')
			{
				$contact_option_label .= $row['contact_organization'];
			}

			if($row['contact_name_given'] != '' || $row['contact_name_family'] != '' || $row['contact_nickname'] != '')
			{
				$contact_option_label .= ($row['contact_organization'] != '') ? "," : null;
				$contact_option_label .= ($row['contact_name_given'] != '') ? (($row['contact_organization'] != '') ? " " : null).$row['contact_name_given'] : null;
				$contact_option_label .= ($row['contact_name_family'] != '') ? (($row['contact_organization'] != '' || $row['contact_name_given'] != '') ? " " : null).$row['contact_name_family'] : null;
				$contact_option_label .= ($row['contact_nickname'] != '') ? (($row['contact_organization'] != '' || $row['contact_name_given'] != '' || $row['contact_name_family'] != '') ? " (".$row['contact_nickname'].")" : $row['contact_nickname']) : null;
			}

			$contact_option_value_recipient = $contact_option_label;
			$contact_option_value_faxnumber = $row['phone_number'];
			$contact_option_label .= " " . escape(format_phone($row['phone_number']));

			$contacts[] = [
				"key" => $contact_option_label,
				"value" => $contact_option_value_faxnumber."|".$contact_option_value_recipient,
			];
		}

		return view("pages.faxes.send", compact("fax", "contacts"));
	}
}
