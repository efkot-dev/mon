<div class="nav-fiber p10">
<form action="/?do=fiber" method="post" id="savecabel">
<div class="add-kabel">
<input name="act" type="hidden" value="updatekabel">
<label class="pol"><span class="val">[lang:kolirvog]:</span><span class="dal"><input class="selcolor" type="color" id="color" name="color" value="{color}"></span></label>
<label class="pol"><span class="val">[lang:modelkable]:</span><span class="dal"><input class="input2" type="text" id="name" name="name" value="{name}"/></span></label>
<label class="pol"><span class="val">[lang:countvolokon]:</span><span class="dal"><input class="input2 w50" type="text" id="volokon" name="volokon" value="{volokon}"  readonly></span></label>
<label class="pol"><span class="val">[lang:countmodule]:</span><span class="dal"><input class="input2 w50" type="text" id="modules" name="modules" value="{modules}"  readonly></span></label>    
</div> 
<div id="colors">
{listfiber}
</div>
<div class="polebtn">
<button type="submit" form="savecabel" value="submit">[lang:save]</button>
</div>
</form>
</div>
