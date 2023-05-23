<h1>Datei konvertieren</h1>

<form action="#" method="POST" enctype="multipart/form-data" >
	<div class="DSEdit">
		<div class="DSFeld4">Datei:<br> <input name="uploadFile"  type=file></div>
		<div class="DSFeld2">Profil:<br> 
			<select name="profilName">
				<?php 
				print "<option value='' >[Bitte Profil wählen]</option>\n";
				foreach($profiles as $name => $file) {
					print "<option value='".$name."' >".$name."</option>\n";
					};
				?>
			</select>		
		</div>
		<div class="DSFeld2 right" style="background: #AA5555;"><input type="submit" name="convert" value="Konvertieren" onclick="wartemal('on')"></div>
	</div>
</form>
