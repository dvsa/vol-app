OLCS.ready(function() {
  var isReason = function (value) {
    return value == 'piwo_reason';
  };

  var isDecision = function (value) {
    return value == 'piwo_decision';
  };

  var isNeither = function (value) {
    return value == 'piwo_none';
  };

  OLCS.showHideInput({
    'source': 'select[name="fields[writtenOutcome]"]',
    'dest': 'label[for="fields[tcWrittenReasonDate]"]',
    'predicate': isReason
  });

  OLCS.showHideInput({
    'source': 'select[name="fields[writtenOutcome]"]',
    'dest': 'label[for="fields[writtenReasonLetterDate]"]',
    'predicate': isReason
  });

  OLCS.showHideInput({
    'source': 'select[name="fields[writtenOutcome]"]',
    'dest': 'label[for="fields[tcWrittenDecisionDate]"]',
    'predicate': isDecision
  });

  OLCS.showHideInput({
    'source': 'select[name="fields[writtenOutcome]"]',
    'dest': 'label[for="fields[decisionLetterSentDate]"]',
    'predicate': isDecision
  });

  OLCS.showHideInput({
    'source': 'select[name="fields[writtenOutcome]"]',
    'dest': 'label[for="fields[decSentAfterWrittenDecDate]"]',
    'predicate': isNeither
  });
});
