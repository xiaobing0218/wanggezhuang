<?php if(!class_exists("View", false)) exit("no direct access allowed");?><tr>
  <th>配置参数</th>
  <td><input type="hidden" name="pcode" value="wxpay" />
    <table class="dataform">
      <tr>
        <th width="110">微信公众号(开发者ID)</th>
        <td><input class="w300 txt" name="params[appid]" id="appid" type="text" value="<?php echo htmlspecialchars($rs['params']['appid'], ENT_QUOTES, "UTF-8"); ?>" /></td>
      </tr>
      <tr>
        <th>微信公众号(开发者密码)</th>
        <td><input class="w300 txt" name="params[secret]" id="secret" type="text" value="<?php echo htmlspecialchars($rs['params']['secret'], ENT_QUOTES, "UTF-8"); ?>" /></td>
      </tr>
      <tr>
        <th>商户号</th>
        <td><input class="w300 txt" name="params[mch_id]" id="mch_id" type="text" value="<?php echo htmlspecialchars($rs['params']['mch_id'], ENT_QUOTES, "UTF-8"); ?>" /></td>
      </tr>
      <tr>
        <th>支付key</th>
        <td><input class="w300 txt" name="params[pay_key]" id="pay_key" type="text" value="<?php echo htmlspecialchars($rs['params']['pay_key'], ENT_QUOTES, "UTF-8"); ?>" /></td>
      </tr>
    </table>
  </td>
</tr>