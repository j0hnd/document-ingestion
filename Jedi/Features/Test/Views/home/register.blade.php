@extends('layouts.default')

@section('title')
    @parent
    {{ $title }}
@stop

@section('content')
<br>
<center>{{ $title }} 
<hr>

<table>
    <form id="frm">
        <tr>
            <td> <label>First Name</label> </td> 
            <td><input type="text" name="first_name" /> </td>
        </tr>
        <tr>
            <td> <label>Last Name</label> </td>
            <td> <input type="text" name="last_name" /> </td>
        </tr>
        <tr>
            <td> <label>Username</label> </td>
            <td> <input type="text" name="username" /> </td>
        </tr>
        <tr>
            <td> <label>Password</label> </td>
            <td> <input type="password" name="password" /> </td>
        </tr>
        <tr>
            <td> <label>Account Type</label> </td>
            <td> 
                <select name='account_type'>
                    <option value='faculty'>Faculty</option>
                    <option value='student'>Student</option>
                </select>
            </td>
        </tr>
        <tr>
            <td> <label>Gender</label> </td>
            <td> 
                <select name='gender'>
                    <option value='female'>Female</option>
                    <option value='male'>Male</option>
                </select>
            </td>
        </tr>
        <tr>
            <td> <label>College</label> </td>
            <td> 
                <select name='college'>
                    <option value='CCS'>College of Computer Studies</option>
                    <option value='CON'>College of Nursing</option>
                    <option value='CED'>College of Education</option>

                </select>
            </td>
        </tr>
        <tr>
            <td> <label>Address</label> </td>
            <td> <textarea name="address"></textarea> </td>
        </tr>
        <tr>
            <td colspan=2> <center> <input type="button" id="register" value="save" /> </td>
        </tr>
        
    </form>
</table>

</center>

<input type="hidden" id="token" name="_token" value="{{ csrf_token() }}" />

@stop

@section('js')
	<script src="{{ asset('/libs/classes/register.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            Register.form();
        });
    </script>
@stop