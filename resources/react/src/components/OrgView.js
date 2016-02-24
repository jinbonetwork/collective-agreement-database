import React from 'react';

export default class OrgView extends React.Component {
  render() {
    return (
      <div>
        OrgView : {this.props.params.orgId}
        {this.props.children}
      </div>
    );
  }
}
