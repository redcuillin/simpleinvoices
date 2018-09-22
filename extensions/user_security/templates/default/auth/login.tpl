{* preload the headers (for faster browsing) *}
{include file='templates/default/header.tpl'}

  <div class="si_wrap">
    <form action="" method="post" id="frmLogin" name="frmLogin">
      <input type="hidden" name="action" value="login" />
      <div class="si_box">
        <div style="float:left;margin:0 auto;width:100%;border:none;">
          {$comp_logo_lines}
          {$comp_name_lines}
        </div>
        <div class="si_box_auth_pad">
          <table>
            <tr>
              <th>{$LANG.id}</th>
              <td><input name="user" size="25" type="text" title="user" value="" /></td>
            </tr>
            <tr>
              <th>{$LANG.password}</th>
              <td><input name="pass" size="25" type="password" title="password" value="" /></td>
            </tr>
            <tr>
              <th></th>
              <td class='td_error'>
                {if $errorMessage }
                <div class="si_error_line">{$errorMessage|outhtml}</div>
                {/if}
              </td>
            </tr>
          </table>
          <div class="si_toolbar">
            <button type="submit" value="login">Login</button>
          </div>
        </div>
      </div>
    </form>
  </div>
  <div id="si_footer">
    <div class="si_wrap">
      <a href="https://simpleinvoices.group>{$LANG.powered_by}&nbsp;{$LANG.simple_invoices}</a>
    </div>
  </div>
  {literal}
  <script>
      $(document).ready(function() {
          $('.si_box').hide();
          $('.si_box').slideDown(500);
      });
      document.frmLogin.user.focus();
  </script>
  {/literal}
</body>
</html>
