/**
 * OLCS.tableRows
 *
 * grunt test:single --target=tableRows
 */

describe('OLCS.tableRows', function() {
  
  'use strict';

  beforeEach(function() {
    this.component = OLCS.tableRows;
  });

  it('should be a function', function() {
    expect(this.component).to.be.a('function');
  });

  describe('When invoked', function() {

    beforeEach(function() {
      this.component();
    });

    afterEach(function() {
      $(document).off('click', 'tbody tr');
      $(document).off('mousenter', 'tbody tr');
      $(document).off('mouseleave', 'tbody tr');
    });

    describe('Given a stubbed DOM with a table row which contains a single action', function() {

      beforeEach(function() {
        $('body').append([
          '<tbody id="tbody">',
            '<tr id="tr1" class="js-rows">',
              '<td id="td1"><a href="#" id="l1"></a></td>',
              '<td id="td2"></td>',
              '<td id="td3"><input type="checkbox" id="cb1"></td>',
            '</tr>',
          '</tbody>'
        ].join('\n'));
      });

      afterEach(function() {
        $('#tbody').remove();
      });

      describe('When the table row is clicked whilst the ctrl key is pressed', function() {

        beforeEach(function() {
          // setup the event handlers
          var simPress = $.Event('keydown');
          var simClick = $.Event('click');
          // simulate ctrl key press
          simPress.ctrlKey = true;
          simClick.ctrlKey = true;
          // fire the events
          $('#tr1').trigger(simPress);
          $('#tr1').trigger(simClick);
          $('#tr1').trigger('contextmenu');
        });

        afterEach(function() {
          $(document).trigger('keyup');
        });

        it.skip('should not open the context menu', function() {
          expect($('#tr1')).not.toHandle('contextmenu');
        });

      }); // When a table row is clicked whilst the ctrl key is pressed

    }); // Given a stubbed DOM with a table row which contains a single action

    describe('Given a stubbed DOM with a table row which contains more than one action', function() {

      beforeEach(function() {
        $('body').append([
          '<tbody id=tbody>',
            '<tr id=tr1>',
              '<td id=td1><a href=# id=l1></a></td>',
              '<td id=td2></td>',
              '<td id=td3><a href=# id=l2></a></td>',
              '<td id=td4><input type=radio id=r1></td>',
            '</tr>',
          '</tbody>'
        ].join('\n'));
      });

      afterEach(function() {
        $('#tbody').remove();
      });

      describe('When the table row is clicked', function() {
        beforeEach(function() {
          this.buttonClickSpy = sinon.spy();
          $('#td1').on('click', this.buttonClickSpy);
          $('#td2').click();
        });

        afterEach(function(){
          $('#td1').off('click');
        });

        it('doesn`t trigger the click of its main action', function() {
          expect(this.buttonClickSpy.callCount).to.equal(0);
        });
      });

    }); // Given a stubbed DOM with a table row which contains more than one action

    describe('Given a stubbed DOM with a table row which contains a select element', function() {

      beforeEach(function() {
        $('body').append([
          '<tbody id="tbody">',
            '<tr id="tr1">',
              '<td id="td1"><a href=#></a></td>',
              '<td id="td2"><input type="checkbox" id="cb1"></td>',
            '</tr>',
            '<tr id="tr2">',
              '<td id="td3"><a href=#></a></td>',
              '<td id="td4"><input type="checkbox" id="cb2"></td>',
            '</tr>',
          '</tbody>'
        ].join('\n'));
      });

      afterEach(function() {
        $('#tbody').remove();
      });

      describe('When a table row is clicked whilst the ctrl key is pressed', function() {

        beforeEach(function() {
          $('#cb1').prop('checked', false).change();
          $('#cb2').prop('checked', false).change();
          var shiftClick = $.Event('click');
          shiftClick.shiftKey = true;
          $('#tr1').trigger(shiftClick);
        });

        afterEach(function(){
          $(document).trigger('keyup');
        });

        it.skip('#cb1 should be checked', function() {
          expect($('#cb1').is(':checked')).to.equal(true);
        });

        it.skip('#cb2 should be checked', function() {
          expect($('#cb2').is(':checked')).to.equal(true);
        });

      }); // When a table row is clicked whilst the ctrl key is pressed

    }); // Given a stubbed DOM with a table row which contains a select element

  }); // When invoked

});
