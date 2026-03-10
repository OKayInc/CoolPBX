<?php

namespace Database\Seeders;

use App\Models\ConferenceRoom;
use App\Models\ConferenceRoomUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CallBlockSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conferenceRoomCount = ConferenceRoom::all()->count();
        $pinNullCount = ConferenceRoom::whereNull('moderator_pin')->whereNull('participant_pin')->count();
        $hasMeetingTable = Schema::hasTable('v_meetings');

        if ($hasMeetingTable && ($conferenceRoomCount > 0) && ($pinNullCount > 0))
        {
            $sql = "UPDATE ".ConferenceRoom::getTableName()." ";
            $sql .= "SET participant_pin = subquery.participant_pin, moderator_pin = subquery.moderator_pin ";
            $sql .= "FROM ( ";
            $sql .= "	SELECT ";
            $sql .= "	r.conference_room_uuid, r.conference_room_name, ";
            $sql .= "	m.moderator_pin, m.participant_pin ";
            $sql .= "	FROM ".ConferenceRoom::getTableName()." as r, v_meetings as m ";
            $sql .= 	"WHERE r.meeting_uuid = m.meeting_uuid  ";
            $sql .= "	) AS subquery ";
            $sql .= "WHERE ".ConferenceRoom::getTableName().".conference_room_uuid = subquery.conference_room_uuid;";
            DB:statement($sql);
        }

        $conferenceRoomUserCount = ConferenceRoomUser::all()->count();
        $hasMeetingUserTable = Schema::hasTable('v_meeting_users');
        $meetingUserCount = $hasMeetingUserTable ? DB::table('v_meeting_users')->count() : 0;

        if ($hasMeetingUserTable && ($conferenceRoomUserCount > 0) && ($meetingUserCount > 0))
        {
            $sql = "INSERT INTO ".ConferenceRoomUser::getTableName()." ( ";
            $sql .= "	domain_uuid, conference_room_user_uuid, conference_room_uuid, user_uuid ";
            $sql .= ") ";
            $sql .= "SELECT r.domain_uuid, m.meeting_user_uuid as conference_room_user_uuid, r.conference_room_uuid, m.user_uuid ";
            $sql .= "FROM ".ConferenceRoomUser::getTableName()." as r, v_meeting_users as m ";
            $sql .= "WHERE r.meeting_uuid = m.meeting_uuid; ";
            DB:statement($sql);
        }
    }
}
