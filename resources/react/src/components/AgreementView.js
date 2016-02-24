import React from 'react';

export default class AgreementView extends React.Component {
  render() {
    return (
      <div>
        AgreementView : {this.props.params.agreementId}
        {this.props.children}
      </div>
    );
  }
}
