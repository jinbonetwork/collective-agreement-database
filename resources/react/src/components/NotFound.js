import React from 'react';

export default class NotFound extends React.Component {
  render() {
    return (
      <div>
        NotFound
        {this.props.children}
      </div>
    );
  }
}
