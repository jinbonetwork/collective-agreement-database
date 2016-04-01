import React, { Component } from 'react';
import axios from 'axios';

import { pageList, showSearching, hideSearching } from '../util/utils';

import StandardList from './Standard/StandardList';
import ArticleList from './Article/ArticleList';
import PageList from './Page/PageList';

export default class Articles extends Component {
  constructor() {
    super();
    this.state = {
      result: {},
	  standards: [],
      articles: [],
	  pages: [],
    };
  }

  render() {
    return (
      <div className="search-result">
        <div className="intermediate-result">
		  <StandardList key={`guide-clause-list`} standards = {this.state.standards} />
          <div className="organ-article-result">
            <ArticleList
              result={this.state.result}
              articles={this.state.articles}
            />
          </div>
		  <PageList
		    pages={this.state.pages}
		  />
        </div>
      </div>
    );
  }

  componentWillMount() {
    this.doSearch(true);
  }

  componentWillReceiveProps() {
    this.doSearch(false);
	window.$('body').animate({scrollTop:0}, '500');
  }

  doSearch(init) {
    const api = '/api/articles';
    const query = window.location.search;
    if(init === true) {
      var url = `${api}${query}&mode=init`;
    } else {
      var url = `${api}${query}`;
    }

    showSearching();
    axios.get(url)
    .then(({ data }) => {
      hideSearching();
	  const pages = pageList(data.result.articles);

      if(init === true) {
        this.setState({
          result: data.result || {},
		  standards: data.standard || [],
          articles: data.articles || [],
          pages: pages || [],
        });
      } else {
        this.setState({
          result: data.result || {},
          articles: data.articles || [],
          pages: pages || [],
        });
      }
    });
  }
}
