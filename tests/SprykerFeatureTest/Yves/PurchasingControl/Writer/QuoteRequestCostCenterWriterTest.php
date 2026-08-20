<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Yves\PurchasingControl\Writer;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\QuoteRequestResponseTransfer;
use Generated\Shared\Transfer\QuoteRequestTransfer;
use Generated\Shared\Transfer\QuoteRequestVersionTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Client\QuoteRequest\QuoteRequestClientInterface;
use SprykerFeature\Yves\PurchasingControl\Writer\QuoteRequestCostCenterWriter;

/**
 * @group SprykerFeatureTest
 * @group Yves
 * @group PurchasingControl
 * @group Writer
 * @group QuoteRequestCostCenterWriterTest
 */
class QuoteRequestCostCenterWriterTest extends Unit
{
    /**
     * @uses \Spryker\Zed\QuoteRequest\Business\Writer\QuoteRequestWriter::GLOSSARY_KEY_QUOTE_REQUEST_WRONG_STATUS
     */
    protected const string GLOSSARY_KEY_QUOTE_REQUEST_NOT_EDITABLE = 'quote_request.validation.error.wrong_status';

    protected const int ID_COST_CENTER = 1;

    protected const int ID_BUDGET = 2;

    protected const int ID_COST_CENTER_OTHER = 3;

    protected const int ID_BUDGET_OTHER = 4;

    /**
     * Both values are chosen in a single submit, so assigning the cost center must not drop the
     * budget that came with it.
     */
    public function testUpdateCostCenterStoresBudgetTogetherWithTheFirstCostCenterSelection(): void
    {
        // Arrange
        $quoteRequestTransfer = $this->createQuoteRequest();

        // Act
        $quoteRequestResponseTransfer = $this->createWriter()->updateCostCenter(
            $quoteRequestTransfer,
            static::ID_COST_CENTER,
            static::ID_BUDGET,
        );

        // Assert
        $quoteTransfer = $quoteRequestTransfer->getLatestVersionOrFail()->getQuoteOrFail();

        $this->assertTrue($quoteRequestResponseTransfer->getIsSuccessful());
        $this->assertSame(static::ID_COST_CENTER, $quoteTransfer->getIdCostCenter());
        $this->assertSame(static::ID_BUDGET, $quoteTransfer->getIdBudget());
    }

    public function testUpdateCostCenterStoresBudgetWhenCostCenterIsChangedToAnotherOne(): void
    {
        // Arrange
        $quoteRequestTransfer = $this->createQuoteRequest(static::ID_COST_CENTER, static::ID_BUDGET);

        // Act
        $this->createWriter()->updateCostCenter(
            $quoteRequestTransfer,
            static::ID_COST_CENTER_OTHER,
            static::ID_BUDGET_OTHER,
        );

        // Assert
        $quoteTransfer = $quoteRequestTransfer->getLatestVersionOrFail()->getQuoteOrFail();

        $this->assertSame(static::ID_COST_CENTER_OTHER, $quoteTransfer->getIdCostCenter());
        $this->assertSame(static::ID_BUDGET_OTHER, $quoteTransfer->getIdBudget());
    }

    public function testUpdateCostCenterRejectsQuoteRequestThatIsNotEditable(): void
    {
        // Arrange
        $quoteRequestTransfer = $this->createQuoteRequest();
        $quoteRequestClientMock = $this->createMock(QuoteRequestClientInterface::class);
        $quoteRequestClientMock->method('isQuoteRequestEditable')->willReturn(false);
        $quoteRequestClientMock->expects($this->never())->method('updateQuoteRequest');

        // Act
        $quoteRequestResponseTransfer = (new QuoteRequestCostCenterWriter($quoteRequestClientMock))->updateCostCenter(
            $quoteRequestTransfer,
            static::ID_COST_CENTER,
            static::ID_BUDGET,
        );

        // Assert
        $quoteTransfer = $quoteRequestTransfer->getLatestVersionOrFail()->getQuoteOrFail();

        $this->assertFalse($quoteRequestResponseTransfer->getIsSuccessful());
        $this->assertSame(
            static::GLOSSARY_KEY_QUOTE_REQUEST_NOT_EDITABLE,
            $quoteRequestResponseTransfer->getMessages()->offsetGet(0)->getValue(),
        );
        $this->assertNull($quoteTransfer->getIdCostCenter());
        $this->assertNull($quoteTransfer->getIdBudget());
    }

    protected function createWriter(): QuoteRequestCostCenterWriter
    {
        $quoteRequestClientMock = $this->createMock(QuoteRequestClientInterface::class);
        $quoteRequestClientMock->method('isQuoteRequestEditable')->willReturn(true);
        $quoteRequestClientMock
            ->method('updateQuoteRequest')
            ->willReturn((new QuoteRequestResponseTransfer())->setIsSuccessful(true));

        return new QuoteRequestCostCenterWriter($quoteRequestClientMock);
    }

    protected function createQuoteRequest(?int $idCostCenter = null, ?int $idBudget = null): QuoteRequestTransfer
    {
        $quoteTransfer = (new QuoteTransfer())
            ->setIdCostCenter($idCostCenter)
            ->setIdBudget($idBudget);

        return (new QuoteRequestTransfer())
            ->setLatestVersion((new QuoteRequestVersionTransfer())->setQuote($quoteTransfer));
    }
}
