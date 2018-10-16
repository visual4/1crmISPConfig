<?php return; ?>

hooks
    before_save
        --
            class: v4Kundennummer
            class_function: account_before_save
            file: modules/v4Kundennummer/v4Kundennummer.php

fields
    v4_kundennummer
        vname: LBL_V4_KDNR
        type: varchar
        len: 20
        updateable: false