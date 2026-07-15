<?php

it('advertises the passkey management endpoints', function () {
    $this->get('/.well-known/passkey-endpoints')
        ->assertOk()
        ->assertJsonStructure(['enroll', 'manage']);
});
