import React from 'react';
import { Link } from 'react-router';
import axios from 'axios';
import SearchForm from './SearchForm';

export default class Main extends React.Component {
  constructor() {
    super();
    this.doSearch = this.doSearch.bind(this);
  }
  render() {
    return (
      <div className="container">
        <SearchForm doSearch={this.doSearch} />
        <br />
        <div className="well">
          {this.props.children}
        </div>
      </div>
    );
  }
  doSearch(keyword) {
    this.context.router.push(`/search?q=${keyword}`);
  }
}

Main.contextTypes = { router: React.PropTypes.object };
