import React, { Component } from 'react';
import axios from 'axios';

export default class Article extends Component {
  constructor() {
    super();
    this.state = {
      article: {},
    };
  }

  render() {
    if (!this.state.article.subject) {
      return <div />;
    }
    return (
      <div className="whole-document">
        <div className="meta-info-wrap">
          <div className="meta-info">
            <div className="column label">
              <div className="row">교섭형태</div>
              <div className="row">{this.state.article.f28[0].name}</div>
              <div className="row">협약체결일</div>
              <div className="row">유효기간</div>
            </div>
            <div className="column info">
              <div className="row">{this.state.article.f28[0].name}</div>
              <div className="row">{this.state.article.f28[1].name}</div>
              <div className="row">{this.state.article.f31}</div>
              <div className="row">{this.state.article.f32}년</div>
            </div>
          </div>
        </div>
        <div className="document">
          <h1>{this.state.article.subject}</h1>
          <div
            dangerouslySetInnerHTML={{ __html: this.state.article.content }}
          />
        </div>
      </div>
    );
  }

  componentWillMount() {
    console.log('- Article componentWillMount');
    this.doSearch();
  }

  doSearch() {
    const api = '/api/articles';
    const aid = this.props.params.aid;
    const url = `${api}/${aid}`;

    axios.get(url)
    .then(({ data }) => {
      console.log(window.location.pathname, url, data);
      // TODO: checkLogin

      this.setState({
        article: data.articles
      });
    });
  }
}
