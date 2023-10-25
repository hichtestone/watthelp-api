<?php

declare(strict_types=1);

namespace App\Tests\Functional\Security\Voter;

use App\Security\Voter\PermissionVoter;
use App\Tests\FunctionalWebTestCase;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @group functional
 * @group permission
 * @group security
 * @group voter
 * @group permission-voter
 */
class PermissionVoterTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testAccessGranted(string $userEmail, $permissions): void
    {
        $user = $this->getUser($userEmail);
        $token = new JWTUserToken([], $user);

        $permissionVoter = self::$container->get(PermissionVoter::class);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $permissionVoter->vote($token, null, [$permissions]));
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testAccessDenied(string $userEmail, $permissions): void
    {
        $user = $this->getUser($userEmail);
        $token = new JWTUserToken([], $user);

        $permissionVoter = self::$container->get(PermissionVoter::class);
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $permissionVoter->vote($token, null, [$permissions]));
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testAccessAbstain(string $userEmail, $permissions): void
    {
        $user = $this->getUser($userEmail);
        $token = new JWTUserToken([], $user);

        $permissionVoter = self::$container->get(PermissionVoter::class);
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $permissionVoter->vote($token, null, [$permissions]));
    }
}