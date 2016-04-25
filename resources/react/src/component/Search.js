import React, { Component } from 'react';
import axios from 'axios';
import StandardList from './Standard/StandardList';
import ArticleList from './Article/ArticleList';
import OrgList from './Org/OrgList';
import { showSearching, hideSearching } from '../util/utils';

export default class Search extends Component {
  constructor() {
    super();
    this.state = {
      fields: {},
      result: {},
      articles: [],
      orgs: [],
      standards: [],
	  org: {},
    };
  }

  render() {
    const cname = (this.state.standards.length > 0) ? 'intermediate-result' : 'intermediate-result full';
    return (
      <div className="search-result">
        <div className={cname}>
          <StandardList key={`guide-clause-list`} standards = {this.state.standards} />

          <div className="organ-article-result">
            <OrgList
              result={this.state.result}
              orgs={this.state.orgs}
            />
            <ArticleList
              result={this.state.result}
              articles={this.state.articles}
            />
          </div>
        </div>
      </div>
    );
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.shouldSearch) {
      this.doSearch();
    }
  }

  componentWillMount() {
    this.doSearch();
  }

  componentDidMount() {
    if(this.state.standards.length > 0) {
	}
  }

  doSearch() {
    const api = '/api/all';
    const query = window.location.search;
    const url = `${api}${query}`;

    showSearching('gray');
    axios.get(url)
    .then(({ data }) => {
      hideSearching();
      this.setState({
        fields: data.fields || {},
        result: data.result || {},
        articles: data.articles || [],
        orgs: data.orgs || [],
        standards: data.standard || [],
      });
      this.props.unsetShouldSearch();
    });
  }
}
