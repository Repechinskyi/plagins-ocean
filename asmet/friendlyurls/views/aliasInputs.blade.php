<div class="form-group">
  <label class="col-xs-3" for="raw_url">Raw url:</label>
  <div class="col-xs-9">
  <input class="form-control"
         id="raw_url"
         name="raw_url"
         value="{{ old('raw_url', !empty($alias) ? $alias->raw_url : '') }}">
  </div>
</div>