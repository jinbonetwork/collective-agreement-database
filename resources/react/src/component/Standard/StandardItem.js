import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import { Link } from 'react-router';
import Standardv from './Standardv';

export default class StandardItem extends Component {
  constructor(props) {
    super(props);
    this.state = {
      standard: props.standard,
    };
  }

  render() {
    return (
      <li>
        <div className="header">{this.state.standard.subject}</div>
        <div className="content"
          dangerouslySetInnerHTML={{ __html: this.state.standard.content }} />
        <div className="footer">
          <button type="button" name="read-article-comment" onClick={this.onClickGuideClause.bind(this)}>조문해설 보기</button>
        </div>
      </li>
    );
  }

  onClickGuideClause() {
    const props = {
      id: this.state.standard.id,
    }
	ReactDOM.render(<Standardv key={`guide-clause-overlay-${this.state.standard.id}`} {...props} />,document.getElementById('overlay-container'));
  }
};
