<tr>
    <td class="details_screen si_right nowrap" style="padding-right: 10px; width: 47%;">
        <label for="displayDetailId">{$LANG.display} {$LANG.detail}:</label>
    </td>
    <td><input type="checkbox" name="displayDetail" id="displayDetailId"
        {if isset($smarty.post.displayDetail) && $smarty.post.displayDetail == "yes"} checked {/if} value="yes"/>
    </td>
</tr>
