<form id='fieldname_selector' style="display:none; padding-left: 50px; padding-right: 50px; padding-top: 20px; "> 
    <fieldset style="padding-left: 15px; height: 250px"> <!-- first box -->

        <table>
            <tr>
                <td width="800">
                    @include('Maps::partials.fieldname-list')
                </td>
                <td width="50px" style="font-size: 20px"><center> >> </center></td>
                <td width="50%">
                    @include('Maps::partials.fieldname-selected')
                </td>
            </tr>
        </table>

    </fieldset>
</form>

