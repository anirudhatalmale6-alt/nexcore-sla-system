{{--
|--------------------------------------------------------------------------
| NexCore System Master Messages
|--------------------------------------------------------------------------
|
| Component:     system_messages
| Version:       1.0
| Location:      resources/views/nexcore/system_master_messages.blade.php
|
| This is the single include file for the NexCore message box system.
| It loads all required dependencies: SweetAlert2 CDN, the message box
| CSS, and the NxAlert JavaScript object.
|
| USAGE:
|   Add this one line to any blade view to enable NxAlert message boxes:
|
|   @include('nexcore.system_master_messages')
|
| AVAILABLE METHODS:
|   NxAlert.success(title, message)    - M1: Success confirmation
|   NxAlert.error(title, message)      - M2: Error notification
|   NxAlert.warning(title, message)    - M3: Warning notice
|   NxAlert.info(title, message)       - M4: Information notice
|   NxAlert.confirm(title, message)    - M5: Yes/No confirmation
|   NxAlert.delete(title, recordName)  - M6: Typed DELETE confirmation
|
| DEPENDENCIES LOADED:
|   1. SweetAlert2 v11 (CSS + JS from CDN)
|   2. /public/nexcore/system_messages/css/system_master_messages.css
|   3. /public/nexcore/system_messages/js/system_master_messages.js
|
| IMAGE FALLBACK:
|   Icon is loaded from /public/nexcore/system_messages/images/ first.
|   If not found, falls back to /public/nexcore/branding/nexcore-icon.png
|
| NexCore Africa Proprietary Limited
| www.nexcore.africa
|
--}}

{{-- SweetAlert2 Library (CDN) --}}
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

{{-- NexCore Message Box Styles --}}
<link href="/public/nexcore/system_messages/css/system_master_messages.css" rel="stylesheet">

{{-- NexCore Message Box JavaScript --}}
<script src="/public/nexcore/system_messages/js/system_master_messages.js"></script>
