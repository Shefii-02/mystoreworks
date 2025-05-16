<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessType;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\Mail\UserCreate;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserCompany;
use App\Models\Utility;
use File;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Session;
use Spatie\Permission\Models\Role;
use Lab404\Impersonate\Impersonate;


class  CompanyController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth'); // Ensure user is authenticated
        // Apply permission-based middleware for specific methods
        $this->middleware('can:manage company')->only(['index']);
        $this->middleware('can:create company')->only(['create', 'store']);
        $this->middleware('can:edit company')->only(['edit', 'update']);
        $this->middleware('can:delete company')->only(['destroy']);
    }

    public function index()
    {
        $users = User::where('type', '=', 'company')->get();
        return view('admin.company.index')->with('users', $users);
    }


    public function create()
    {
        $customFields = CustomField::where('created_by', '=', Auth::user()->creatorId())->where('module', '=', 'user')->get();
        $user           = Auth::user();
        $business_types  = BusinessType::get();
        $plans          = Plan::get();
        return view('admin.company.form', compact('customFields', 'business_types', 'plans'));
    }

    public function store(Request $request)
    {
        $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->first();
        $company_default_language = DB::table('settings')->select('value')->where('name', 'company_default_language')->first();
        // $date = DB::table('settings')->select('value')->where('name', 'email_verification')->first();

        $userpassword               = $request->input('password');

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|max:120',
                'email' => 'required|email|unique:users',
                'bussiness_type' => 'required|max:120',
                'address' => 'required|max:250',
                'landmark' => 'required|max:250',
                'postalcode' => 'required|max:250',
                'city' => 'required|max:250',
                'identify_code' => 'required|max:10',
                'plan'   => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $enableLogin       = 0;
        if (!empty($request->password_switch) && $request->password_switch == 'on') {
            $enableLogin   = 1;
            $validator = Validator::make(
                $request->all(),
                ['password' => 'required|min:6']
            );

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }
        }

        $plan               = Plan::where('id', $request->plan)->first();

        $user               = new User();
        $user['name']       = $request->name;
        $user['email']      = $request->email;
        $user['mobile']     = $request->mobile;
        $user['email_verified_at'] = date('Y-m-d H:i:s');
        $psw                = $request->password;
        $user['password'] = !empty($userpassword) ? Hash::make($userpassword) : null;
        $user['type']       = 'company';
        $user['lang']       = !empty($default_language) ? $default_language->value : '';
        $user['created_by'] = Auth::user()->creatorId();
        $user['plan']       = $plan->id;
        $user['is_enable_login'] = $enableLogin;
        $user['referral_code'] = Utility::generateReferralCode();
        $user->save();

        $company                = new Company();
        $company->user_id       = $user->id;
        $company->bussiness_name = $request->name ?? '';
        $company->bussiness_type = $request->bussiness_type ?? '';
        $company->address       = $request->address;
        $company->landmark      = $request->landmark;
        $company->postalcode    = $request->postalcode;
        $company->city          = $request->city;
        $company->country       = 'UAE';
        $company->identify_code =  $request->identify_code;
        $company->save();

        CustomField::saveData($user, $request->customField);

        $role_r = Role::findByName('company');
        $user->assignRole($role_r);


        Company::planOrderStore($plan,$company->id);


        $user->userDefaultDataRegister($user->id);
        Utility::chartOfAccountTypeData($user->id);
        Utility::chartOfAccountData1($user->id);


        $uArr = [
            'email' => $user->email,
            'password' => $psw,
        ];

        try {
            $resp = Utility::sendEmailTemplate('user_created', [$user->id => $user->email], $uArr);
        } catch (\Exception $e) {
            $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
        }


        $uArr = [
            'email' => $user->email,
            'password' => $psw,
        ];

        try {
            $resp = Utility::sendEmailTemplate('user_created', [$user->id => $user->email], $uArr);
        } catch (\Exception $e) {
            $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
        }

        return redirect()->route('admin.company.index')->with('success', __('User successfully added.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
    }

    public function edit($id)
    {

        $user  = Auth::user();
        $business_types    = BusinessType::get();
        $plans             = Plan::get();
        $user              = User::findOrFail($id);
        $user->customField = CustomField::getData($user, 'user');
        $customFields      = CustomField::where('created_by', '=', Auth::user()->creatorId())->where('module', '=', 'user')->get();

        return view('admin.company.form', compact('user', 'customFields', 'business_types', 'plans'));
    }


    public function update(Request $request, $id)
    {
            $user = User::findOrFail($id);

            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:120',
                    'email' => 'required|email|unique:users,email,' . $id,
                    'bussiness_type' => 'required|max:120',
                    'address' => 'required|max:250',
                    'landmark' => 'required|max:250',
                    'postalcode' => 'required|max:250',
                    'city' => 'required|max:250',
                    'identify_code' => 'required|max:10',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $input = $request->all();
            $user->fill($input)->save();

            $company                = Company::where('user_id', $user->id)->first();
            $company->bussiness_name = $request->name ?? '';
            $company->bussiness_type = $request->bussiness_type ?? '';
            $company->address       = $request->address;
            $company->landmark      = $request->landmark;
            $company->postalcode    = $request->postalcode;
            $company->city          = $request->city;
            $company->identify_code =  $request->identify_code;
            $company->save();

            CustomField::saveData($user, $request->customField);

            return redirect()->route('users.index')->with(
                'success',
                'User successfully updated.'
            );
        
     
    }


    public function destroy($id)
    {

        $user = User::find($id);

        if ($user) {
           

                User::where('type', '=', 'company')->delete();
                $user->delete();
                return redirect()->back()->with('success', __('Company Successfully deleted'));

                // if ($user->delete_status == 0) {
                //     $user->delete_status = 1;
                // } else {
                //     $user->delete_status = 0;
                // }
                // $user->save();
         

            return redirect()->route('users.index')->with('success', __('User successfully deleted .'));
        } else {
            return redirect()->back();
        }
    }

    public function profile()
    {
        $userDetail              = Auth::user();
        $userDetail->customField = CustomField::getData($userDetail, 'user');
        $customFields            = CustomField::where('created_by', '=', Auth::user()->creatorId())->where('module', '=', 'user')->get();

        return view('admin.company.profile', compact('userDetail', 'customFields'));
    }

    public function editprofile(Request $request)
    {
        $userDetail = Auth::user();
        $user       = User::findOrFail($userDetail['id']);
        $this->validate(
            $request,
            [
                'name' => 'required|max:120',
                'email' => 'required|email|unique:users,email,' . $userDetail['id'],
            ]
        );

        if ($request->hasFile('profile')) {
            if (Auth::user()->type = 'super admin') {
                $file_path = $user['avatar'];
                $filenameWithExt = $request->file('profile')->getClientOriginalName();
                $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                $extension       = $request->file('profile')->getClientOriginalExtension();
                $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                $settings = Utility::getStorageSetting();

                if ($settings['storage_setting'] == 'local') {
                    $dir        = 'uploads/avatar/';
                } else {
                    $dir        = 'uploads/avatar';
                }
                $image_path = $dir . $userDetail['avatar'];

                $url = '';
                // $path = $request->file('profile')->storeAs('uploads/avatar/', $fileNameToStore);
                // dd($path);
                $path = Utility::upload_file($request, 'profile', $fileNameToStore, $dir, []);
                // dd($path);
                if ($path['flag'] == 1) {
                    $url = $path['url'];
                } else {
                    return redirect()->route('profile', Auth::user()->id)->with('error', __($path['msg']));
                }
            } else {
                $file_path = $user['avatar'];
                $image_size = $request->file('profile')->getSize();
                $result = Utility::updateStorageLimit(Auth::user()->creatorId(), $image_size);

                if ($result == 1) {

                    Utility::changeStorageLimit(Auth::user()->creatorId(), $file_path);
                    $filenameWithExt = $request->file('profile')->getClientOriginalName();
                    $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
                    $extension       = $request->file('profile')->getClientOriginalExtension();
                    $fileNameToStore = $filename . '_' . time() . '.' . $extension;
                    $settings = Utility::getStorageSetting();

                    if ($settings['storage_setting'] == 'local') {
                        $dir        = 'uploads/avatar/';
                    } else {
                        $dir        = 'uploads/avatar';
                    }
                    $image_path = $dir . $userDetail['avatar'];

                    $url = '';
                    // $path = $request->file('profile')->storeAs('uploads/avatar/', $fileNameToStore);
                    // dd($path);
                    $path = Utility::upload_file($request, 'profile', $fileNameToStore, $dir, []);
                    // dd($path);
                    if ($path['flag'] == 1) {
                        $url = $path['url'];
                    } else {
                        return redirect()->route('profile', Auth::user()->id)->with('error', __($path['msg']));
                    }
                } else {
                    return redirect()->back()->with('error', $result);
                }
            }
        }

        if (!empty($request->profile)) {
            $user['avatar'] =  $url;
        }
        $user['name']  = $request['name'];
        $user['email'] = $request['email'];
        $user->save();
        CustomField::saveData($user, $request->customField);

        return redirect()->back()->with(
            'success',
            __('Profile successfully updated.') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : '')
        );
    }

    public function updatePassword(Request $request)
    {
        if (Auth::Check()) {
            $request->validate(
                [
                    'current_password' => 'required',
                    'new_password' => 'required|min:6',
                    'confirm_password' => 'required|same:new_password',
                ]
            );
            $objUser          = Auth::user();
            $request_data     = $request->All();
            $current_password = $objUser->password;
            if (Hash::check($request_data['current_password'], $current_password)) {
                $user_id            = Auth::User()->id;
                $obj_user           = User::find($user_id);
                $obj_user->password = Hash::make($request_data['new_password']);;
                $obj_user->save();

                return redirect()->route('profile', $objUser->id)->with('success', __('Password successfully updated.'));
            } else {
                return redirect()->route('profile', $objUser->id)->with('error', __('Please enter correct current password.'));
            }
        } else {
            return redirect()->route('profile', Auth::user()->id)->with('error', __('Something is wrong.'));
        }
    }

    public function upgradePlan($user_id)
    {
        $user = User::find($user_id);

        $plans = Plan::get();

        return view('admin.company.plan', compact('user', 'plans'));
    }

    public function activePlan($user_id, $plan_id)
    {

        $user       = User::find($user_id);
        $assignPlan = $user->assignPlan($plan_id);
        $plan       = Plan::find($plan_id);
        if ($assignPlan['is_success'] == true && !empty($plan)) {
            $orderID = strtoupper(str_replace('.', '', uniqid('', true)));
            Order::create(
                [
                    'order_id' => $orderID,
                    'name' => null,
                    'card_number' => null,
                    'card_exp_month' => null,
                    'card_exp_year' => null,
                    'plan_name' => $plan->name,
                    'plan_id' => $plan->id,
                    'price' => $plan->price,
                    'price_currency' => isset(Auth::user()->planPrice()['currency']) ? Auth::user()->planPrice()['currency'] : 'AED',
                    'txn_id' => '',
                    'payment_status' => 'succeeded',
                    'receipt' => null,
                    'user_id' => $user->company->id,
                ]
            );

            return redirect()->back()->with('success', 'Plan successfully upgraded.');
        } else {
            return redirect()->back()->with('error', 'Plan fail to upgrade.');
        }
    }

    // change mode 'dark or light'
    public function changeMode()
    {
        $usr = Auth::user();
        if ($usr->mode == 'light') {
            $usr->mode      = 'dark';
        } else {
            $usr->mode      = 'light';
        }
        $usr->save();
        return redirect()->back();
    }

    public function userPassword($id)
    {
        $eId        = \Crypt::decrypt($id);
        $user = User::find($eId);

        return view('admin.company.reset', compact('user'));
    }

    public function userPasswordReset(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(),
            [
                'password' => 'required|confirmed|same:password_confirmation',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        $user                 = User::where('id', $id)->first();
        $user->forceFill([
            'password' => Hash::make($request->password),
            'is_enable_login' => 1,
        ])->save();

        return redirect()->route('users.index')->with(
            'success',
            'User Password successfully updated.'
        );
    }

    public function LoginWithCompany(Request $request,   $id)
    {
        // dd($request,  $request->user(), $id);
        $user = User::find($id);
        if ($user && auth()->check()) {
            Impersonate::take($request->user(), $user);
            return redirect('/');
        }
    }

    public function ExitCompany(Request $request)
    {
        Auth::user()->leaveImpersonation($request->user());
        return redirect('/');
    }

    public function CompnayInfo($id)
    {
        if (!empty($id)) {
            $data = $this->Counter($id);
            if ($data['is_success']) {
                $users_data = $data['response']['users_data'];
                return view('admin.company.companyinfo', compact('id', 'users_data'));
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function UserUnable(Request $request)
    {
        if (!empty($request->id) && !empty($request->company_id)) {
            if ($request->name == 'user') {
                User::where('id', $request->id)->update(['is_disable' => $request->is_disable]);
                $data = $this->Counter($request->company_id);
            }

            if ($data['is_success']) {
                $users_data = $data['response']['users_data'];
            }
            if ($request->is_disable == 1) {

                return response()->json(['success' => __('Successfully Enable.'), 'users_data' => $users_data]);
            } else {
                return response()->json(['success' => __('Successfull Disable.'), 'users_data' => $users_data]);
            }
        }
        return response()->json('error');
    }


    public function Counter($id)
    {
        $response = [];
        if (!empty($id)) {

            $users = User::where('created_by', $id)->selectRaw('COUNT(*) as total_users, SUM(CASE WHEN is_disable = 0 THEN 1 ELSE 0 END) as disable_users, SUM(CASE WHEN is_disable = 1 THEN 1 ELSE 0 END) as active_users')->first();

            $users_data[$users->name] = [
                'total_users' => !empty($users->total_users) ? $users->total_users : 0,
                'disable_users' => !empty($users->disable_users) ? $users->disable_users : 0,
                'active_users' => !empty($users->active_users) ? $users->active_users : 0,
            ];

            $response['users_data'] = $users_data;

            return [
                'is_success' => true,
                'response' => $response,
            ];
        }
        return [
            'is_success' => false,
            'error' => 'Plan is deleted.',
        ];
    }

    public function LoginManage($id)
    {
        $eId        = Crypt::decrypt($id);
        $user = User::find($eId);
        if ($user->is_enable_login == 1) {
            $user->is_enable_login = 0;
            $user->save();
            return redirect()->back()->with('success', __('User login disable successfully.'));
        } else {
            $user->is_enable_login = 1;
            $user->save();
            return redirect()->back()->with('success', __('User login enable successfully.'));
        }
    }
}
