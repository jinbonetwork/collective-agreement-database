import React, { Component } from 'react';

export default class Home extends Component {
  render() {
    return (
      <div>
        검색어를 입력해 주세요.
        {this.props.children}
      </div>
    );
  }
}
