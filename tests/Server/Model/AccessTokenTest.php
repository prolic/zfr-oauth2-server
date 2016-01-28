<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace ZfrOAuth2Test\Server\Model;

use ZfrOAuth2\Server\Model\AccessToken;
use ZfrOAuth2\Server\Model\Client;
use ZfrOAuth2\Server\Model\TokenOwnerInterface;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 * @covers  \ZfrOAuth2\Server\Model\AbstractToken
 * @covers  \ZfrOAuth2\Server\Model\AccessToken
 */
class AccessTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerGenerateNew
     */
    public function testGenerateNew($ttl, $owner, $client, $scopes)
    {
        /** @var AccessToken $accessToken */
        $accessToken = AccessToken::generateNew($ttl, $owner, $client, $scopes);

        $expiresAt = (new \DateTimeImmutable())->modify("+$ttl seconds");

        $this->assertNotEmpty($accessToken->getToken());
        $this->assertEquals(40, strlen($accessToken->getToken()));
        $this->assertCount(count($scopes), $accessToken->getScopes());
        $this->assertSame($client, $accessToken->getClient());
        $this->assertEquals($expiresAt, $accessToken->getExpiresAt());
        $this->assertSame($owner, $accessToken->getOwner());
    }

    public function providerGenerateNew()
    {
        return [
            [
                3600,
                $this->getMock(TokenOwnerInterface::class),
                $this->getMock(Client::class, [], [], '', false),
                ['scope1', 'scope2']
            ],
            [
                3600,
                $this->getMock(TokenOwnerInterface::class),
                $this->getMock(Client::class, [], [], '', false),
                'scope1'
            ],
            [3600, null, null, null]
        ];
    }

    /**
     * @dataProvider providerReconstitute
     */
    public function testReconstitute($data)
    {
        /** @var AccessToken $accessToken */
        $accessToken = AccessToken::reconstitute($data);


        $this->assertEquals($data['token'], $accessToken->getToken());

        if (isset($data['scopes'])) {
            if (is_string($data['scopes'])) {
                $data['scopes'] = explode(" ", $data['scopes']);
            }
            $this->assertCount(count($data['scopes']), $accessToken->getScopes());
        } else {
            $this->assertTrue(is_array($accessToken->getScopes()));
            $this->assertEmpty($accessToken->getScopes());
        }

        if (isset($data['owner'])) {
            $this->assertSame($data['owner'], $accessToken->getOwner());
        } else {
            $this->assertNull($accessToken->getOwner());
        }

        if (isset($data['client'])) {
            $this->assertSame($data['client'], $accessToken->getClient());
        } else {
            $this->assertNull($accessToken->getClient());
        }

        if (isset($data['expiresAt'])) {
            $this->assertInstanceOf(\DateTimeImmutable::class, $accessToken->getExpiresAt());
            $this->assertSame(($data['expiresAt'])->getTimeStamp(), $accessToken->getExpiresAt()->getTimestamp());
        } else {
            $this->assertNull($accessToken->getExpiresAt());
        }
    }


    public function providerReconstitute()
    {
        return [
            [
                [ // data set with all options
                  'token'     => 'token',
                  'owner'     => $this->getMock(TokenOwnerInterface::class),
                  'client'    => $this->getMock(Client::class, [], [], '', false),
                  'expiresAt' => new \DateTimeImmutable(),
                  'scopes'    => ['scope1', 'scope2'],
                ]
            ],
            [
                [ // data set with minimum options
                  'token'     => 'token',
                  'owner'     => null,
                  'client'    => null,
                  'expiresAt' => null,
                  'scopes'    => null,
                ]
            ],
            [
                [ // data set with minimum options
                  'token'  => 'token',
                  'scopes' => 'read write',
                ]
            ],
        ];
    }
//
//    public function testGettersAndSetters()
//    {
//        $owner     = $this->getMock(TokenOwnerInterface::class);
//        $client    = new Client('id', 'name', 'secret', ['http://www.example.com']);
//        $expiresAt = new DateTime();
//
//        $accessToken = new AccessToken();
//        $accessToken->setToken('token');
//        $accessToken->setScopes(['scope1', 'scope2']);
//        $accessToken->setClient($client);
//        $accessToken->setExpiresAt($expiresAt);
//        $accessToken->setOwner($owner);
//
//        $this->assertEquals('token', $accessToken->getToken());
//        $this->assertCount(2, $accessToken->getScopes());
//        $this->assertTrue($accessToken->matchScopes('scope1'));
//        $this->assertFalse($accessToken->matchScopes('scope3'));
//        $this->assertSame($client, $accessToken->getClient());
//        $this->assertEquals($expiresAt, $accessToken->getExpiresAt());
//        $this->assertSame($owner, $accessToken->getOwner());
//    }
//
//    public function testCanSetScopesFromString()
//    {
//        $scopes = 'foo bar';
//
//        $accessToken = new AccessToken();
//        $accessToken->setScopes($scopes);
//
//        $this->assertCount(2, $accessToken->getScopes());
//    }
//
//    public function testCanSetScopesFromInstances()
//    {
//        $scope = new Scope(1, 'bar');
//
//        $accessToken = new AccessToken();
//        $accessToken->setScopes([$scope]);
//
//        $this->assertCount(1, $accessToken->getScopes());
//    }
//
//    public function testCalculateExpiresIn()
//    {
//        $expiresAt = new DateTime();
//        $expiresAt->add(new DateInterval('PT60S'));
//
//        $accessToken = new AccessToken();
//        $accessToken->setExpiresAt($expiresAt);
//
//        $this->assertFalse($accessToken->isExpired());
//        $this->assertEquals(60, $accessToken->getExpiresIn());
//    }
//
//    public function testCanCheckIfATokenIsExpired()
//    {
//        $expiresAt = new DateTime();
//        $expiresAt->sub(new DateInterval('PT60S'));
//
//        $accessToken = new AccessToken();
//        $accessToken->setExpiresAt($expiresAt);
//
//        $this->assertTrue($accessToken->isExpired());
//    }
//
//    public function testSupportLongLiveToken()
//    {
//        $accessToken = new AccessToken();
//        $this->assertFalse($accessToken->isExpired());
//    }
}
