<?php

namespace App\Exports;

use App\Models\Call;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;


class CallsExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Call::select("type", "flag", "idtperson", "username", "first_name", "last_name", "complete_start_time", "complete_stop_time", "complete_duration", "complete_id", "participant_phone_number", "servico", "hang_up_side_description", "hang_up_side_id", "complete_total_transfers_count")->get();
    }

    /**
     * Write code on Method
     *
     * @return response()
     */

    public function headings(): array
    {
        return ["ID", "Type", "Flag", "idtperson", "username", "First Name", "Last Name", "Complete Start Time", "Complete StopTime", "Complete Duration", "Complete ID", "Participant Phone Number", "servico", "HangUp Side Description", "Hang Up Side ID", "Complete Total Transfers Count"];
    }
}
