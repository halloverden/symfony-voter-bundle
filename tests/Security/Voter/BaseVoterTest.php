<?php

namespace HalloVerden\VoterBundle\Tests\Security\Voter;

use HalloVerden\VoterBundle\Security\Security;
use HalloVerden\VoterBundle\Tests\Security\Voter\Mock\BaseVoterMock;
use HalloVerden\VoterBundle\Tests\Security\Voter\Mock\Subject2Mock;
use HalloVerden\VoterBundle\Tests\Security\Voter\Mock\SubjectMock;
use PHPUnit\Framework\TestCase;

class BaseVoterTest extends TestCase {

  public function testSupports_supported_shouldReturnTrue() {
    $voter = new BaseVoterMock($this->createMock(Security::class));
    $supports = $voter->supports('test', [new SubjectMock()]);

    $this->assertTrue($supports);
  }

  public function testSupports_NotSupported_shouldReturnFalse() {
    $voter = new BaseVoterMock($this->createMock(Security::class));
    $supports = $voter->supports('no', [new SubjectMock()]);

    $this->assertFalse($supports);
  }

  public function testSortSubjects_subjects_shouldReturnSortedSubjects() {
    $voter = new BaseVoterMock($this->createMock(Security::class));
    $subjects = $voter->sortSubjects([new Subject2Mock(), new SubjectMock()], [SubjectMock::class, Subject2Mock::class]);

    $this->assertCount(2, $subjects);
    $this->assertInstanceOf(SubjectMock::class, $subjects[0]);
    $this->assertInstanceOf(Subject2Mock::class, $subjects[1]);
  }

}
