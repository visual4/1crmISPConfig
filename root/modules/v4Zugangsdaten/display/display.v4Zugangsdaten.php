<?php return; ?>

fields
    pw_decrypted
        widget: v4PasswordWidget
    pw_form
        widget: v4PWFormField
widgets
    v4PasswordWidget
        path: modules/v4Zugangsdaten/widgets/v4PasswordWidget.php
        type: column
    v4PWFormField
        path: modules/v4Zugangsdaten/widgets/v4PWFormField.php
        type: field