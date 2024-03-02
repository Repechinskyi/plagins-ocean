<div class="form-group">
<label class="col-xs-3" for="title">Title
  <code><sup>
      <small>&lsaquo;meta&rsaquo;</small>
    </sup></code>
  :</label>
<div class="col-xs-9">
<input class="form-control"
       id="title"
       name="title"
       value="{{ old('title', !empty($meta) ? $meta->title : '') }}">
</div>
</div>


<div class="form-group">
<label class="col-xs-3" for="menu">for Menu
  <code><sup>
      <small>&lsaquo;meta&rsaquo;</small>
    </sup></code>
  :</label>
<div class="col-xs-9">
<input class="form-control"
       id="menu"
       name="menu"
       value="{{ old('menu', !empty($meta) ? $meta->menu : '') }}">
</div>
</div>


<div class="form-group">
<label class="col-xs-3" for="description">Description
  <code><sup>
      <small>&lsaquo;meta&rsaquo;</small>
    </sup></code>
  :</label>
<div class="col-xs-9">
<textarea class="form-control"
          id="description"
          name="description"
          rows="2">{{ old('description', !empty($meta) ? $meta->description : '') }}</textarea>
</div>
</div>