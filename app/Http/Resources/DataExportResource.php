<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\UserProfile;
use App\Models\ChatGallery;
use App\Models\ChatRoom;
use App\Models\CreatorVerification;
use App\Models\AccountSuspension;


class DataExportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
         /*
        Data Export Columns: User, First Name, Last Name, Phone Number, Email, Chat Gallery Name, 
        Chat Room Name, Video Name, Account Type (Admirer, Creator - Tier 1, Creator - Tier 2), Role, Account Status
        */
        $name=""; $phone=""; $email = ""; $chat_gallery_name = ""; $chat_room_name = ""; $tier ="Tier 1"; $account_status = "Active";

        if($user_profile = UserProfile::where('user_id', $this->id)->first()){
            $name = $user_profile?->name;
            $phone = $user_profile?->phone;
            $email = $user_profile?->email;
        }

        if($chat_gallery = ChatGallery::where('user_id', $this->id)->first()){
            $chat_gallery_name = $chat_gallery?->name;
        }

        if($chat_room = ChatRoom::where('user_id', $this->id)->first()){
            $chat_room_name = $chat_room?->name;
        }

        if($verification = CreatorVerification::where('user_id', $this->id)->first()){
            $tier = $verification->tier;
        }

        if($suspension = AccountSuspension::where('user_id', $this->id)->first()){
            $account_status = "Suspended";
        }

        return [
            'id'                => $this->id,
            'name'              => $name,
            'phone'             => $phone,
            'email'             => $email,
            'chat_gallery_name' => $chat_gallery_name,
            'chat_room_name'    => $chat_room_name,
            'tier'              => $tier,
            'account_status'    => $account_status,
        ];
        

    }
}
