import React, { Component } from 'react';
import axios from 'axios';
import StandardList from './Standard/StandardList';
import ArticleList from './Article/ArticleList';
import OrgList from './Org/OrgList';

export default class Search extends Component {
  constructor() {
    super();
    this.state = {
      result: {},
      articles: [],
      orgs: [],
      standards: [],
    };
  }

  render() {
    return (
      <div className="search-result">
        <div className="intermediate-result">
          <StandardList standards={this.state.standards} />

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

  doSearch() {
    const api = '/api/all';
    const query = window.location.search;
    const url = `${api}${query}`;

    axios.get(url)
    .then(({ data }) => {
      console.log(window.location.pathname, url, data);
      // TODO: checkLogin

      this.setState({
        result: data.result || {},
        articles: data.articles || [],
        orgs: data.orgs || [],
        standards: data.standard || [],
      });
      this.props.unsetShouldSearch();
    });
  }
}
